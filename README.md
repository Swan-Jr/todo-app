# todo-app
Simple Todo application implementing MVC architecrute


## Functionality
* Creating and editing tasks
* Pagination
* Sorting

## Default admin credentials:
* Login: admin
* Password: admin1234

## Installation
1. Clone or download this repository
2. Install dependencies via composer
3. Create Database (optionally), add table with such schema:
```SQL
CREATE TABLE `[your_db_name]`.`[your_table_name]` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `status` ENUM('A', 'NA') NOT NULL,
  PRIMARY KEY (`id`));
```
4. Specify all DB settings at 'app/config.php'
5. Run it
