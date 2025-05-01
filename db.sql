DROP DATABASE IF EXISTS Progetto_Chen;
CREATE DATABASE Progetto_Chen;
USE Progetto_Chen;

CREATE TABLE Users (
    ID INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    Nome VARCHAR(50) NOT NULL,
    Cognome VARCHAR(50) NOT NULL,
    Username VARCHAR(30) NOT NULL UNIQUE,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Password_hash VARCHAR(60) NOT NULL,
    Tipo ENUM('user', 'admin') DEFAULT 'user',
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    Updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Materia(
    ID INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    Nome VARCHAR(100) NOT NULL
);

INSERT INTO materia (Nome) VALUES 
("Italiano"),
("Matematica"),
("Storia"),
("Gestione Progetto Organizzazione d'Impresa"),
("Tecnologie Progettazione Sistemi Informatici e Telecomunicazioni"),
("Informatica"),
("Inglese"),
("Scienze Motorie"),
("Sistemi e Reti"), 
("Religione");

CREATE TABLE Argomento(
    ID INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    Nome VARCHAR(100) NOT NULL
);

CREATE TABLE Notes (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    User_id INT NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Materia_ID INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    Content LONGTEXT,
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    Updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (User_id) REFERENCES Users(ID) ON DELETE CASCADE, 
    FOREIGN KEY (Materia_ID) REFERENCES Materia(ID) ON DELETE CASCADE
);

CREATE TABLE appunti_argomento(
    IDNote INT NOT NULL, 
    IDArgomento INT NOT NULL, 
    PRIMARY KEY (IDNote, IDArgomento),
    FOREIGN KEY (IDNote) REFERENCES Notes(ID) ON DELETE CASCADE,
    FOREIGN KEY (IDArgomento) REFERENCES Argomento(ID) ON DELETE CASCADE
);

CREATE TABLE Files (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    Note_id INT NOT NULL,
    User_id INT NOT NULL,
    Original_filename VARCHAR(255) NOT NULL,
    Stored_filename VARCHAR(255) UNIQUE NOT NULL,
    Mime_type VARCHAR(50) NOT NULL,
    File_size INT NOT NULL,
    Created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Note_id) REFERENCES Notes(ID) ON DELETE CASCADE,
    FOREIGN KEY (User_id) REFERENCES Users(ID) ON DELETE CASCADE
);