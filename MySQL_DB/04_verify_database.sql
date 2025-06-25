-- Use the Camellia database
USE Camellia;

-- Verify database structure
SHOW TABLES;

-- Display table structures
DESCRIBE Users;
DESCRIBE Post;
DESCRIBE CandidatesResult;

-- Display sample data with relationships
SELECT 
    cr.CandidateNationalId,
    CONCAT(cr.FirstName, ' ', cr.LastName) AS FullName,
    cr.Gender,
    cr.DateOfBirth,
    p.PostName,
    cr.ExamDate,
    cr.PhoneNumber,
    cr.Marks,
    CASE 
        WHEN cr.Marks >= 90 THEN 'Excellent'
        WHEN cr.Marks >= 80 THEN 'Very Good'
        WHEN cr.Marks >= 70 THEN 'Good'
        WHEN cr.Marks >= 60 THEN 'Pass'
        ELSE 'Fail'
    END AS Grade
FROM CandidatesResult cr
JOIN Post p ON cr.PostId = p.PostId
ORDER BY cr.Marks DESC;

-- Display statistics
SELECT 
    p.PostName,
    COUNT(cr.CandidateNationalId) AS TotalCandidates,
    AVG(cr.Marks) AS AverageMarks,
    MAX(cr.Marks) AS HighestMarks,
    MIN(cr.Marks) AS LowestMarks
FROM Post p
LEFT JOIN CandidatesResult cr ON p.PostId = cr.PostId
GROUP BY p.PostId, p.PostName
ORDER BY AverageMarks DESC;
