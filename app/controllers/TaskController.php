<?php

namespace Controllers;


use Models\Auth;
use Models\Task;

class TaskController extends Controller
{
    private $taskInstance;

    /**
     * TaskController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        Auth::Authenticate();

        $this->taskInstance = new Task();
        $this->twig->addGlobal('user_name', $_SESSION['username'] ?? 'anon');
        $this->twig->addGlobal('isLoggedIn', Auth::isLoggedIn());
    }

    /**
     * Task list pages method
     * Valid URIs: /index.php, /index.php?page={1.999}, /, /?page={1.999}
     *
     * @return mixed
     */
    public function index()
    {
        $taskList = [];
        $pagesArray = [];
        $tableSort = [];

//        Tasks
        $targetArray = $this->taskInstance->getTaskList();
        foreach ($targetArray as $key => $value) {
            $taskList[$key]['username'] = $targetArray[$key]['username'] ?? '';
            $taskList[$key]['email'] = $targetArray[$key]['email'] ?? '';
            $taskList[$key]['content'] = $targetArray[$key]['content'] ?? '';
            $taskList[$key]['status'] = $targetArray[$key]['status'] ?? '';
            $taskList[$key]['editLink'] = ROOT_PREFIX . $targetArray[$key]['id'] . "/show" ?? '';
            $taskList[$key]['canEdit'] = ($_SESSION['userrole'] ?? '') === Auth::getValidUserRoles()[1];
        }

//        Pages
        for ($i = 1; $i <= $this->taskInstance->getPagesAll(); $i++) {
            $pagesArray[$i]['active'] = ($i === $this->taskInstance->getPagesCurrent());
            $pagesArray[$i]['number'] = $i;
            $pagesArray[$i]['ref'] = $this->taskInstance->getPagesLinks()[$i];
        }

//        Table headers' sort rules
        $tableSort['column'] = $_SESSION['sortrules']['sortby'] ?? '';
        $tableSort['order'] = $_SESSION['sortrules']['order'] ?? '';

//        Sort form options
        $formOptions['username'] = ($tableSort['column'] === 'username' ? 'selected' : ' ');
        $formOptions['email'] = ($tableSort['column'] === 'email' ? 'selected' : ' ');
        $formOptions['status'] = ($tableSort['column'] === 'status' ? 'selected' : ' ');
        $formOptions['asc'] = ($tableSort['order'] === 'asc' ? 'selected' : ' ');
        $formOptions['desc'] = ($tableSort['order'] === 'desc' ? 'selected' : ' ');
        $formOptions['default'] = in_array('selected', $formOptions) ? ' ' : '';

        return $this->twig->render('tasklist.html.twig', [
            'taskList' => $taskList,
            'pages' => $pagesArray,
            'currentUri' => $_SERVER['REQUEST_URI'],
            'tableSort' => $tableSort,
            'formOptions' => $formOptions
        ]);
    }

    /**
     * Show task method
     * Valid URIs: /{1..999}/show
     *
     * @param int $taskID
     * @return mixed
     */
    public function show(int $taskID)
    {
        // here we may put grants check
        Auth::restrictAccess(Auth::getValidUserRoles()[1]);

        $task = $this->taskInstance->getOneTask($taskID);

        $renderedTask['username'] = $task['username'] ?? '';
        $renderedTask['email'] = $task['email'] ?? '';
        $renderedTask['content'] = $task['content'] ?? '';
        $renderedTask['status'] = ($task['status'] === 'A' ? 'checked' : '');

        return $this->twig->render('task.html.twig', [
            'new' => false,
            'task' => $renderedTask,
            'formAction' => $_SERVER['REQUEST_URI'],
            'backRef' => $_SESSION['backRef'] ?? ROOT_PREFIX
        ]);
    }

    /**
     * Create task method
     * Valid URI: /task/new
     *
     * @return mixed
     */
    public function new()
    {

        return $this->twig->render('task.html.twig', [
            'new' => true,
            'formAction' => $_SERVER['REQUEST_URI'],
            'backRef' => $_SESSION['backRef'] ?? ROOT_PREFIX
        ]);
    }

    /**
     * Saving task to DB method
     * Valid POST URIs: /task/new, /{1..999}/show
     *
     * @param int|null $taskID
     * @return mixed
     */
    public function store(int $taskID = null)
    {
        if ($taskID !== null) Auth::restrictAccess(Auth::getValidUserRoles()[1]);

        if ( !Task::isValidInput() ) {

            return $this->twig->render('task.html.twig', [
                'new' => false,
                'task' => [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'content' => $_POST['content'],
                    'status' => $_POST['status'],
                ],
                'formAction' => $_SERVER['REQUEST_URI'],
                'backRef' => $_SESSION['backRef'] ?? ROOT_PREFIX,
                'errors' => true,
            ]);
        }

        $result = $this->taskInstance->saveTask($taskID);
        $redirectMessage = $result['success']
            ? 'Вы будете перемещены на главную страницу'
            : 'Попробуйте еще раз. Вы будете перемещены обратно';
        $redirectPath = $result['success']
            ? $_SESSION['backRef'] ?? ROOT_PREFIX
            : $_SERVER['REQUEST_URI'];

        return $this->twig->render('redirect.html.twig', [
            'message' => $result['message'],
            'redirectMessage' => $redirectMessage,
            'redirectPath' => $redirectPath,
            'success' => $result['success'],
        ]);
    }
}