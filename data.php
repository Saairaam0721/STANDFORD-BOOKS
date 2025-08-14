<?php
// data.php - Database connection and data management functions

// Database configuration
class Database {
    private $host = 'localhost';
    private $db_name = 'stanford_books';
    private $username = 'root';
    private $password = '';
    private $conn;
    
    // Database connection
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// User management class
class UserManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all users
    public function getAllUsers() {
        $query = "SELECT * FROM users ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Search users by name
    public function searchUsersByName($name) {
        $query = "SELECT * FROM users WHERE name LIKE :name ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $searchTerm = '%' . $name . '%';
        $stmt->bindParam(':name', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get user's borrowing history
    public function getUserBorrowingHistory($userId) {
        $query = "SELECT bh.*, b.title, b.author, b.isbn 
                  FROM borrowing_history bh 
                  JOIN books b ON bh.book_id = b.id 
                  WHERE bh.user_id = :user_id 
                  ORDER BY bh.date_returned DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Add new user
    public function addUser($name, $email) {
        $query = "INSERT INTO users (name, email, created_at) VALUES (:name, :email, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }
}

// Book management class
class BookManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all available books
    public function getAvailableBooks() {
        $query = "SELECT * FROM books WHERE status = 'available' ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all borrowed books
    public function getBorrowedBooks() {
        $query = "SELECT b.*, cb.user_id, cb.due_date, u.name as borrower_name 
                  FROM books b 
                  JOIN current_borrowings cb ON b.id = cb.book_id 
                  JOIN users u ON cb.user_id = u.id 
                  WHERE b.status = 'borrowed' 
                  ORDER BY cb.due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all books
    public function getAllBooks() {
        $query = "SELECT * FROM books ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Search books by title or author
    public function searchBooks($searchTerm) {
        $query = "SELECT * FROM books 
                  WHERE title LIKE :search OR author LIKE :search 
                  ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bindParam(':search', $searchParam);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Add new book
    public function addBook($title, $author, $isbn, $genre) {
        $query = "INSERT INTO books (title, author, isbn, genre, status, created_at) 
                  VALUES (:title, :author, :isbn, :genre, 'available', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':author', $author);
        $stmt->bindParam(':isbn', $isbn);
        $stmt->bindParam(':genre', $genre);
        return $stmt->execute();
    }
    
    // Borrow a book
    public function borrowBook($bookId, $userId, $dueDate) {
        try {
            $this->conn->beginTransaction();
            
            // Update book status
            $query1 = "UPDATE books SET status = 'borrowed' WHERE id = :book_id";
            $stmt1 = $this->conn->prepare($query1);
            $stmt1->bindParam(':book_id', $bookId);
            $stmt1->execute();
            
            // Add to current borrowings
            $query2 = "INSERT INTO current_borrowings (book_id, user_id, borrow_date, due_date) 
                       VALUES (:book_id, :user_id, NOW(), :due_date)";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bindParam(':book_id', $bookId);
            $stmt2->bindParam(':user_id', $userId);
            $stmt2->bindParam(':due_date', $dueDate);
            $stmt2->execute();
            
            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    // Return a book
    public function returnBook($bookId, $userId) {
        try {
            $this->conn->beginTransaction();
            
            // Get borrowing info
            $query = "SELECT * FROM current_borrowings WHERE book_id = :book_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':book_id', $bookId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($borrowing) {
                // Add to borrowing history
                $query1 = "INSERT INTO borrowing_history (book_id, user_id, borrow_date, date_returned) 
                           VALUES (:book_id, :user_id, :borrow_date, NOW())";
                $stmt1 = $this->conn->prepare($query1);
                $stmt1->bindParam(':book_id', $bookId);
                $stmt1->bindParam(':user_id', $userId);
                $stmt1->bindParam(':borrow_date', $borrowing['borrow_date']);
                $stmt1->execute();
                
                // Remove from current borrowings
                $query2 = "DELETE FROM current_borrowings WHERE book_id = :book_id AND user_id = :user_id";
                $stmt2 = $this->conn->prepare($query2);
                $stmt2->bindParam(':book_id', $bookId);
                $stmt2->bindParam(':user_id', $userId);
                $stmt2->execute();
                
                // Update book status
                $query3 = "UPDATE books SET status = 'available' WHERE id = :book_id";
                $stmt3 = $this->conn->prepare($query3);
                $stmt3->bindParam(':book_id', $bookId);
                $stmt3->execute();
                
                $this->conn->commit();
                return true;
            }
            
            $this->conn->rollback();
            return false;
        } catch(Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}

// Statistics class
class StatsManager {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get library statistics
    public function getLibraryStats() {
        $stats = array();
        
        // Total books
        $query = "SELECT COUNT(*) as total FROM books";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_books'] = $result['total'];
        
        // Available books
        $query = "SELECT COUNT(*) as available FROM books WHERE status = 'available'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['available_books'] = $result['available'];
        
        // Borrowed books
        $query = "SELECT COUNT(*) as borrowed FROM books WHERE status = 'borrowed'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['borrowed_books'] = $result['borrowed'];
        
        // Active members
        $query = "SELECT COUNT(*) as members FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['active_members'] = $result['members'];
        
        return $stats;
    }
    
    // Get overdue books
    public function getOverdueBooks() {
        $query = "SELECT b.title, b.author, u.name as borrower_name, u.email, cb.due_date
                  FROM books b 
                  JOIN current_borrowings cb ON b.id = cb.book_id 
                  JOIN users u ON cb.user_id = u.id 
                  WHERE cb.due_date < CURDATE() 
                  ORDER BY cb.due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Sample data insertion (for initial setup)
function insertSampleData($conn) {
    try {
        // Sample users
        $users = [
            ['John Smith', 'john.smith@stanford.edu'],
            ['Emily Johnson', 'emily.johnson@stanford.edu'],
            ['Michael Brown', 'michael.brown@stanford.edu'],
            ['Sarah Davis', 'sarah.davis@stanford.edu'],
            ['David Wilson', 'david.wilson@stanford.edu']
        ];
        
        foreach ($users as $user) {
            $query = "INSERT INTO users (name, email, created_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute($user);
        }
        
        // Sample books
        $books = [
            ['The Adventures of Tom Sawyer', 'Mark Twain', '978-0486400778', 'Classic Fiction', 'available'],
            ['Jane Eyre', 'Charlotte Brontë', '978-0486424491', 'Classic Romance', 'available'],
            ['Moby Dick', 'Herman Melville', '978-0486430943', 'Adventure', 'available'],
            ['The Great Gatsby', 'F. Scott Fitzgerald', '978-0486280608', 'Classic Fiction', 'borrowed'],
            ['To Kill a Mockingbird', 'Harper Lee', '978-0486424323', 'Drama', 'borrowed'],
            ['1984', 'George Orwell', '978-0486424361', 'Dystopian Fiction', 'borrowed']
        ];
        
        foreach ($books as $book) {
            $query = "INSERT INTO books (title, author, isbn, genre, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->execute($book);
        }
        
        return true;
    } catch(Exception $e) {
        return false;
    }
}

// API endpoints for AJAX calls
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'search_users':
            $userManager = new UserManager($conn);
            $searchTerm = $_POST['search_term'] ?? '';
            $users = $userManager->searchUsersByName($searchTerm);
            echo json_encode(['success' => true, 'data' => $users]);
            break;
            
        case 'get_user_by_id':
            $userManager = new UserManager($conn);
            $userId = $_POST['user_id'] ?? '';
            $user = $userManager->getUserById($userId);
            if ($user) {
                $history = $userManager->getUserBorrowingHistory($userId);
                $user['borrowing_history'] = $history;
                echo json_encode(['success' => true, 'data' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            break;
            
        case 'get_stats':
            $statsManager = new StatsManager($conn);
            $stats = $statsManager->getLibraryStats();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// SQL for creating database tables
/*
CREATE DATABASE IF NOT EXISTS stanford_books;
USE stanford_books;

CREATE TABLE users (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    genre VARCHAR(50),
    status ENUM('available', 'borrowed') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE current_borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    user_id VARCHAR(10),
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE borrowing_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT,
    user_id VARCHAR(10),
    borrow_date DATE NOT NULL,
    date_returned DATE NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
    // Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'library_user');  // or 'root' if you didn't create a user
define('DB_PASS', 'your_password_here'); // replace with the password you set
define('DB_NAME', 'library_management');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}
    define('DB_HOST', 'your-public-ip'); // Instead of localhost
);
*/