-- Create the Camellia database
CREATE DATABASE IF NOT EXISTS Camellia;
USE Camellia;

-- Create Users table
CREATE TABLE Users (
    UserId INT AUTO_INCREMENT PRIMARY KEY,
    UserName VARCHAR(100) NOT NULL,
    Password VARCHAR(255) NOT NULL
);

-- Create Post table
CREATE TABLE Post (
    PostId INT AUTO_INCREMENT PRIMARY KEY,
    PostName VARCHAR(100) NOT NULL
);

-- Create CandidatesResult table
CREATE TABLE CandidatesResult (
    CandidateNationalId VARCHAR(16) PRIMARY KEY,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Gender ENUM('Male', 'Female') NOT NULL,
    DateOfBirth DATE NOT NULL,
    PostId INT NOT NULL,
    ExamDate DATE NOT NULL,
    PhoneNumber VARCHAR(15),
    Marks INT NOT NULL,
    
    -- Constraints
    CONSTRAINT chk_marks CHECK (Marks >= 0 AND Marks <= 100),
    
    -- Foreign Key
    CONSTRAINT fk_post FOREIGN KEY (PostId) REFERENCES Post(PostId)
);
