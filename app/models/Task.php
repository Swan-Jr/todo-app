<?php

namespace Models;


class Task extends BaseModel
{
//    Dynamic amount of tasks per page; could be changed to your value: [1..20]
    private $tasksPerPage = 3;

//    When requesting task-list page this variable is automatically filled with all pages figure: [1..999]
    private $pagesAll = null;

//    When requesting task-list page this variable is automatically filled with current page figure: [1..999]
    private $pagesCurrent = null;

//    When requesting task-list page this variable is automatically filled with all pages' links
    private $pagesLinks = null;

    public const INSERT_SUCCESS = 'Новая задача была успешно создана';

    public const UPDATE_SUCCESS = 'Изменения успешно внесены';

    private $errorMessage = '';

    /**
     * @return mixed
     */
    public function getPagesAll()
    {
        return $this->pagesAll;
    }

    /**
     * @return mixed
     */
    public function getPagesCurrent()
    {
        return $this->pagesCurrent;
    }

    /**
     * @return null
     */
    public function getPagesLinks()
    {
        return $this->pagesLinks;
    }

    public function saveTask(int $taskID = null): array
    {
        if ($taskID !== null && $this->updateTask($taskID)) {

            return ['success' => true, 'message' => Task::UPDATE_SUCCESS];
        } elseif ($taskID === null && $this->insertTask()) {

            return ['success' => true, 'message' => Task::INSERT_SUCCESS];
        }

        return ['success' => false, 'message' => $this->errorMessage];
    }

    private function updateTask(int $taskID): bool
    {
        $result = false;

        try {
            $insert = $this->pdo->prepare('UPDATE tasks 
SET 
    username = :username,
    email = :email,
    content = :content,
    status = :status
WHERE
    id = :id;');

            $result = $insert->execute([
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'content' => $_POST['content'],
                'status' => (isset($_POST['status']) && $_POST['status'] === 'on') ? 'A' : 'NA',
                'id' => $taskID
            ]);
        } catch (\PDOException $e) {
            $this->errorMessage = 'Update failed: ' . $e->getMessage();
        } finally {

            return $result;
        }
    }

    private function insertTask(): bool
    {
        $result = false;

        try {
            $insert = $this->pdo->prepare('INSERT INTO tasks
(id, username, email, content, status)
VALUES (null, :username, :email, :content, :status);');

            $result = $insert->execute([
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'content' => $_POST['content'],
                'status' => (isset($_POST['status']) && $_POST['status'] === 'on') ? 'A' : 'NA',
            ]);
        } catch (\PDOException $e) {
            $this->errorMessage = 'Insertion failed: ' . $e->getMessage();
        } finally {

            return $result;
        }
    }

    /**
     * Getting task list for current page
     *
     * @return array
     */
    public function getTaskList(): array
    {
//        Checking post-request sorting rules.
        $sortRule = isset($_POST['sortby']) && isset($_POST['order']) ? $this->parseSort() : '';
//        If no such - checking session sort rules
        if ( ($sortRule === '') && isset($_SESSION['sortrules']) )
            $sortRule = "ORDER BY {$_SESSION['sortrules']['sortby']} {$_SESSION['sortrules']['order']}";

//        Getting all tasks set
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tasks $sortRule;");
            $stmt->execute();
        } catch (\PDOException $e) {
            die('Failed while fetching whole table: ' . $e->getMessage());
        }
        $resultSet = $stmt->fetchAll(\PDO::FETCH_ASSOC);

//        Getting current page, pages links and all pages
        $this->doPagination($resultSet);

//        Filtering output array to make it containing only current page tasks
        $resultPageSet = [];
        $loopArgumentOne = ($this->pagesCurrent - 1) * $this->tasksPerPage;
        for ($i = $loopArgumentOne; $i < ($loopArgumentOne + $this->tasksPerPage) && isset($resultSet[$i]); $i++) {
            $resultPageSet[] = $resultSet[$i];
        }

        return $resultPageSet;
    }

    /**
     * Getting editable certain task info
     *
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function getOneTask(int $id): array
    {
//        Getting certain task
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = :id;");
            $stmt->execute(['id' => $id]);
            $resultSet = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ( !$resultSet ) {
                throw new \Exception('Invalid task number specified');
            }

            return $resultSet;
        } catch (\PDOException $e) {
            die('Failed while searching task: ' . $e->getMessage());
        }
    }

    /**
     * Parsing POST sort data. If one is set, storing it in $_SESSION variable
     *
     * @return string
     * @throws \Exception
     */
    private function parseSort(): string
    {
        $matches = null;
        $patternSort = '/^(username|email|status)$/';
        $patternOrder = '/^(asc|desc)$/';

        if (
            preg_match($patternSort, $_POST['sortby']) === 1 &&
            preg_match($patternOrder, $_POST['order']) === 1
        ) {
            $_SESSION['sortrules'] = ['sortby' => $_POST['sortby'], 'order' => $_POST['order']];

            return "ORDER BY {$_POST['sortby']} {$_POST['order']}";
        } elseif (preg_match('/^default$/', $_POST['sortby']) === 1) {
            unset($_SESSION['sortrules'], $_POST['sortby'], $_POST['order']);

            return '';
        }

        throw new \Exception('Wrong sort arguments sent');
    }

    /**
     * Getting current page, all-page and page-links values.
     * Also storing current page ref in $_SESSION variable
     *
     * @param $input
     */
    private function doPagination(array $input): void
    {
        $tasksAmount = count($input);
        $this->pagesAll = intdiv($tasksAmount, $this->tasksPerPage) + ($tasksAmount % $this->tasksPerPage !== 0 ? 1 : 0);

        if ( isset($_GET['page']) ) {
            $this->validatePage();
            $this->pagesCurrent = (int) $_GET['page'];
        } else {
            $this->pagesCurrent = 1;
        }

//        $_SESSION['backRef'] = ROOT_PREFIX . "?page=$this->pagesCurrent";
        $_SESSION['backRef'] = $_SERVER['REQUEST_URI'];

        for ($i = 1; $i <= $this->pagesAll; $i++) {
            $this->pagesLinks[$i] = ROOT_PREFIX . "?page=$i";
        }
    }

    /**
     * Checking if page figure is valid
     *
     * @throws \Exception
     */
    private function validatePage(): void
    {
        if (!is_numeric($_GET['page']) || $_GET['page'] > $this->pagesAll || $_GET['page'] < 1)
            throw new \Exception('Wrong page arguments sent');
    }

    /**
     * Checking if submitted form data is valid
     *
     * @return bool
     */
    public static function isValidInput(): bool
    {
        $patterns['usernamePattern'] = '/(^[\w]{3,}$)|(^[\w]+( [\w]+)+$)/';
        $patterns['emailPattern'] = '/^[\w\d]+@[\w\d]+\.[\w]+(\.[\w]+){0,2}$/';
        $patterns['contentPattern'] = '/[\w\W]+/';
        $patterns['statusPattern'] = '/^(on)?$/';

        if (
            !isset($_POST['username'])
            || !isset($_POST['email'])
            || !isset($_POST['content'])
            || ( isset($_POST['status']) && preg_match($patterns['statusPattern'], $_POST['status']) !== 1 )
            || preg_match($patterns['usernamePattern'], $_POST['username']) !== 1
            || preg_match($patterns['emailPattern'], $_POST['email']) !== 1
            || preg_match($patterns['contentPattern'], $_POST['content']) !== 1
        ) {

            return false;
        }

        return true;
    }

}
