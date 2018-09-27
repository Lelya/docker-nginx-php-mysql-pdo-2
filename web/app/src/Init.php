<?php
/**
 * Файл класса Init
 *
 * @author Lelya <kovyrshina.olga@yandex.ru>
 * @version 1.0
 */

namespace App\Acme;
use PDO,PDOException;

/**
 * Класс Init
 * от которого нельзя сделать наследника
 */
final class Init
{
        /**
         * @var string адрес сервера mysql
         */
        private $host = "mysql";
        /**
         * @var string имя пользователя
         */
        private $user = "root";
        /**
         * @var string пароль для пользователя
         */
        private $pass = "root";
        /**
         * @var string имя базы данных
         */
        private $database = "main";
        /**
         * @var \PDO соединение с базой
         */
        private $connection;


        function __construct() {
                try {
                        $this->connection = new PDO("mysql:host={$this->host};dbname={$this->database};charset=utf8", $this->user, $this->pass);
                        $this->connection->SetAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch (PDOException $e) {
                        die('Подключение не удалось: ' . $e->getMessage());
                }
                $this->create();
                $this->fill();
        }

        /**
         * Создает таблицу books, содержащую 2 поля
         *   - book_id целое, автоинкрементарное;
         *   - book_name строковое, длиной 25 символов - название книги;
         *
         * Создает таблицу authors, содержащую 2 поля
         *   - authors_id целое, автоинкрементарное;
         *   - author_name строковое, длиной 35 символов - автор;
         *
         * Создает таблицу book_autor, содержащую 2 поля
         *   - book_id целое;
         *   - authors_id целое;
         */
        private function create()
        {
                //запрос на создание таблиц
                $query = $this->connection->prepare("CREATE TABLE IF NOT EXISTS `books` (
                        `book_id` INT NOT NULL AUTO_INCREMENT,
                        `book_name` TEXT NOT NULL,
                        PRIMARY KEY(`book_id`))");
                $query->execute();

                $query = $this->connection->prepare("CREATE TABLE IF NOT EXISTS `authors` (
                        `author_id` INT NOT NULL AUTO_INCREMENT,
                        `author_name` varchar(35) NOT NULL,
                        PRIMARY KEY(`author_id`))");
                $query->execute();

                $query = $this->connection->prepare("CREATE TABLE IF NOT EXISTS book_author (
                        PRIMARY KEY (book_id , author_id ),
                        author_id INT NOT NULL REFERENCES authors(author_id),
                        book_id INT NOT NULL REFERENCES books(book_id) )");
                $query->execute();

        }

        /**
         * Заполняет таблицы books, authors, book_author ;
         */
        private function fill()
        {
                //очищаем таблицы
                $statement = $this->connection->prepare('TRUNCATE TABLE books');
                $statement->execute();
                $statement = $this->connection->prepare('TRUNCATE TABLE authors');
                $statement->execute();
                $statement = $this->connection->prepare('TRUNCATE TABLE book_author');
                $statement->execute();

                //книги
                $statement = $this->connection->prepare('INSERT INTO books (book_name)
                              VALUES ("Двенадцать стульев. Золотой теленок."),
                                     ("Заклинатели"),
                                     ("Республика Шкид"),
                                     ("Физическая химия неводных растворов"),
                                     ("Веселый трамвай"),
                                     ("Ю. Н. Вторичные эталоны единиц измерений ионизирующих излучений")');
                $statement->execute();

                //авторы
                $statement = $this->connection->prepare('INSERT INTO authors (author_name)
                              VALUES ("Ильф И."),
                                     ("Петров Е."),
                                     ("Алексей Пехов"),
                                     ("Елена Бычкова"),
                                     ("Наталья Турчанинов"),
                                     ("Белых Г."),
                                     ("Пантелеев Л."),
                                     ("Н. Я. Фиалков"),
                                     ("А. Н. Житомирский"),
                                     ("Ю. Н. Тарасенко.")');
                $statement->execute();

                //связи
                $statement = $this->connection->prepare('INSERT INTO book_author (author_id, book_id)
                              VALUES (1,1),
                                     (2,1),
                                     (3,2),
                                     (4,2),
                                     (5,2),
                                     (6,3),
                                     (7,3),
                                     (8,4),
                                     (9,4),
                                     (10,4),
                                     (7,5),
                                     (10,6)');
                $statement->execute();

        }

        /**
         * Выбирает из таблицы test, данные по критерию: result среди значений 'normal' и 'success';
         * @return array массив значений
         */
        public function get()
        {
                $statement = $this->connection->prepare("SELECT books.book_name, COUNT(authors.author_id ) AS number FROM books
                                INNER JOIN book_author ON books.book_id = book_author.book_id
                                LEFT JOIN authors ON book_author.author_id = authors.author_id
                                GROUP BY book_author.book_id HAVING count(1)>=3");
                $statement->execute();

                $data = $statement->fetchAll(PDO::FETCH_ASSOC);
                return $data;
        }

}



