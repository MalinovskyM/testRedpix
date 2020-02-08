"# testRedpix" 
Скачать open server
развернуть проэкт в папке `domains`
создать базу данных testRedpix
создать таблицу `comment` поля: 
		* `id` (int, AUTO_INCREMENT)
		* `comment` (text)
создать таблицу `files` поля: 
		* `id` (int, AUTO_INCREMENT)
		* `name` (varchar(255))
		* `id_comment` (int)