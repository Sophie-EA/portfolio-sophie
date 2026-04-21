#CREATE DATABASE Portfolio;

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    technologies VARCHAR(255),
    image VARCHAR(255),
    github_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO admins (username, PASSWORD)
VALUES (
	'Soka',
	'$2y$12$XiRbFPNZz3QBXFUSDVtDmOfEKGgxRrryNEmZvJSaYNKrLrTef/tva'
);	