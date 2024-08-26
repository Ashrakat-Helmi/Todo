CREATE Database `todo_app`;
USE `todo_app`;

-----------------------------------------------------------------------
-----------------------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
);

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'john_doe', 'john@example.com', 'password123', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(2, 'jane_doe', 'jane@example.com', 'password456', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(3, 'alice', 'alice@example.com', 'password789', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(4, 'bob', 'bob@example.com', 'password101', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(5, 'charlie', 'charlie@example.com', 'password202', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(6, 'dave', 'dave@example.com', 'password303', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(7, 'eve', 'eve@example.com', 'password404', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(8, 'frank', 'frank@example.com', 'password505', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(9, 'grace', 'grace@example.com', 'password606', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(10, 'hank', 'hank@example.com', 'password707', '2024-08-26 04:49:06', '2024-08-26 04:49:06'),
(11, 'ash22', 'admin@gmail.com', '$2y$10$fQnGDJX.qszC6PmqVxX0F.mhdlKGQZ3YRiH.M7gBtJgeaaQ9X6QOu', '2024-08-26 05:42:21', '2024-08-26 05:42:21'),
(14, 'ash225', 'ash225@gmail.com', '$2y$10$GaB852yuED9Wi7KSgJoxoe8lpr63yaFDOQAsiXMKhmr9pQZsDPHNO', '2024-08-26 06:25:20', '2024-08-26 06:25:20');

-----------------------------------------------------------------------
-----------------------------------------------------------------------

CREATE TABLE `categories` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE
);

INSERT INTO `categories` (`id`, `name`) VALUES
(6, 'Education'),
(10, 'Entertainment'),
(5, 'Finance'),
(9, 'Fitness'),
(4, 'Health'),
(8, 'Hobbies'),
(2, 'Personal'),
(3, 'Shopping'),
(7, 'Travel'),
(1, 'Work');

-----------------------------------------------------------------------
-----------------------------------------------------------------------

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  FOREIGN key('user_id') REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
);

INSERT INTO `tasks` (`id`, `title`, `user_id`, `description`, `deadline`, `priority`, `category_id`, `created_at`, `updated_at`) VALUES
(22, 'Complete project report', 14, 'Finish the report by end of the week after update', '2024-08-30', 'High', 1, '2024-08-26 06:39:53', '2024-08-26 07:24:43'),
(23, 'Buy groceries', 14, 'Get milk, eggs, and bread', '2024-08-25', 'Medium', 3, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(24, 'Doctor appointment', 14, 'Routine check-up', '2024-08-26', 'High', 4, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(25, 'Pay bills', 14, 'Pay electricity and water bills', '2024-08-28', 'High', 5, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(26, 'Study for exam', 14, 'Prepare for the math exam', '2024-08-29', 'High', 6, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(27, 'Plan vacation', 10, 'Research destinations and book flights', '2024-09-01', 'Medium', 7, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(28, 'Learn guitar', 1, 'Practice guitar for 30 minutes daily', '2024-08-31', 'Low', 8, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(29, 'Go to gym', 4, 'Workout session in the morning', '2024-08-27', 'High', 9, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(30, 'Watch movie', 14, 'Watch the new release on Netflix', '2024-08-24', 'Low', 10, '2024-08-26 06:42:18', '2024-08-26 06:42:18'),
(31, 'Prepare presentation', 14, 'Prepare slides for the upcoming meeting', '2024-08-30', 'Medium', 1, '2024-08-26 06:42:18', '2024-08-26 06:42:18');

-----------------------------------------------------------------------
-----------------------------------------------------------------------

CREATE TABLE `comments` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
);

INSERT INTO `comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`) VALUES
(11, 25, 14, 'remember that', '2024-08-26 07:30:05'),
(13, 26, 14, 'new commentsash', '2024-08-26 09:02:30'),
(14, 22, 14, 'remmmmmber', '2024-08-26 09:49:07'),
(15, 23, 14, 'carrot', '2024-08-26 10:11:48'),
(16, 24, 14, 'appointment at 7 pm', '2024-08-26 15:07:18');

-----------------------------------------------------------------------
-----------------------------------------------------------------------
CREATE TABLE `attachments` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

);

INSERT INTO `attachments` (`id`, `task_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(11, 26, 'garage2.jpg', 'uploads/garage2.jpg', '2024-08-26 07:39:42'),
(12, 24, 'garage1.jpg', '/uploads/garage1.jpg', '2024-08-26 15:08:00');

-----------------------------------------------------------------------
-----------------------------------------------------------------------