<?php
// admin.php - Admin dashboard page
require_once 'data.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

$userManager = new UserManager($conn);
$bookManager = new BookManager($conn);
$statsManager = new StatsManager($conn);

// Get library statistics
$stats = $statsManager->getLibraryStats();
$overdueBooks = $statsManager->getOverdueBooks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Stanford Books</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1>STANFORD BOOKS</h1>
                <p class="tagline">Your library, anytime, anywhere.</p>
            </div>
        </div>
    </header>

    <nav>
        <div class="container">
            <div class="nav-buttons">
                <button class="nav-btn" onclick="window.location.href='index.html'">Home</button>
                <button class="nav-btn active">Admin</button>
                <button class="nav-btn" onclick="window.location.href='index.html#available'">Books Available</button>
                <button class="nav-btn" onclick="window.location.href='index.html#unavailable'">Books Unavailable</button>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="admin-dashboard">
                <h2>Admin Dashboard</h2>
                
                <!-- Statistics Overview -->
                <div class="stats-overview">
                    <h3>Library Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $stats['total_books']; ?></span>
                            <span class="stat-label">Total Books</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $stats['available_books']; ?></span>
                            <span class="stat-label">Available Books</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $stats['borrowed_books']; ?></span>
                            <span class="stat-label">Borrowed Books</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-number"><?php echo $stats['active_members']; ?></span>
                            <span class="stat-label">Active Members</span>
                        </div>
                    </div>
                </div>

                <!-- Admin Search Section -->
                <div class="admin-search-section">
                    <div class="search-tabs">
                        <button class="tab-btn active" onclick="showTab('user-search')">Search Users</button>
                        <button class="tab-btn" onclick="showTab('book-management')">Book Management</button>
                        <button class="tab-btn" onclick="showTab('overdue-books')">Overdue Books</button>
                    </div>

                    <!-- User Search Tab -->
                    <div id="user-search" class="tab-content active">
                        <div class="search-section">
                            <h3>Search User by ID</h3>
                            <div class="search-form">
                                <input type="text" id="userIdSearch" class="search-input" placeholder="Enter user ID..." />
                                <button class="search-btn" onclick="searchUserById()">Search</button>
                            </div>
                        </div>

                        <div class="search-section">
                            <h3>Search User by Name</h3>
                            <div class="search-form">
                                <input type="text" id="adminUserNameSearch" class="search-input" placeholder="Enter user name..." />
                                <button class="search-btn" onclick="adminSearchUserByName()">Search</button>
                            </div>
                        </div>

                        <div id="adminSearchResults" class="results-section" style="display: none;">
                            <h4>Search Results:</h4>
                            <div id="adminResultsContainer"></div>
                        </div>
                    </div>

                    <!-- Book Management Tab -->
                    <div id="book-management" class="tab-content">
                        <div class="management-section">
                            <h3>Add New Book</h3>
                            <form id="addBookForm" class="add-book-form">
                                <div class="form-row">
                                    <input type="text" id="bookTitle" placeholder="Book Title" required>
                                    <input type="text" id="bookAuthor" placeholder="Author" required>
                                </div>
                                <div class="form-row">
                                    <input type="text" id="bookISBN" placeholder="ISBN" required>
                                    <input type="text" id="bookGenre" placeholder="Genre" required>
                                </div>
                                <button type="submit" class="add-btn">Add Book</button>
                            </form>
                        </div>

                        <div class="management-section">
                            <h3>Book Actions</h3>
                            <div class="action-form">
                                <input type="text" id="actionBookId" placeholder="Book ID" required>
                                <input type="text" id="actionUserId" placeholder="User ID" required>
                                <button class="action-btn borrow-btn" onclick="borrowBook()">Borrow Book</button>
                                <button class="action-btn return-btn" onclick="returnBook()">Return Book</button>
                            </div>
                        </div>
                    </div>

                    <!-- Overdue Books Tab -->
                    <div id="overdue-books" class="tab-content">
                        <div class="overdue-section">
                            <h3>Overdue Books</h3>
                            <?php if (count($overdueBooks) > 0): ?>
                                <div class="overdue-list">
                                    <?php foreach ($overdueBooks as $book): ?>
                                        <div class="overdue-item">
                                            <div class="book-info">
                                                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                                <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                                                <p><strong>Borrower:</strong> <?php echo htmlspecialchars($book['borrower_name']); ?></p>
                                                <p><strong>Email:</strong> <?php echo htmlspecialchars($book['email']); ?></p>
                                                <p><strong>Due Date:</strong> <?php echo htmlspecialchars($book['due_date']); ?></p>
                                            </div>
                                            <div class="overdue-status">
                                                <span class="status-badge overdue">OVERDUE</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-overdue">No overdue books at this time.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h3>Recent Activity</h3>
                    <div class="activity-list">
                        <div class="activity-item">
                            <span class="activity-time">2 hours ago</span>
                            <span class="activity-text">John Smith returned "The Great Gatsby"</span>
                        </div>
                        <div class="activity-item">
                            <span class="activity-time">4 hours ago</span>
                            <span class="activity-text">Emily Johnson borrowed "Pride and Prejudice"</span>
                        </div>
                        <div class="activity-item">
                            <span class="activity-time">6 hours ago</span>
                            <span class="activity-text">New book added: "The Catcher in the Rye"</span>
                        </div>
                        <div class="activity-item">
                            <span class="activity-time">1 day ago</span>
                            <span class="activity-text">Michael Brown registered as new member</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Stanford Books. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Tab functionality
        function showTab(tabId) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tab buttons
            const tabBtns = document.querySelectorAll('.tab-btn');
            tabBtns.forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        // Search user by ID functionality
        async function searchUserById() {
            const searchId = document.getElementById('userIdSearch').value.trim();
            const resultsSection = document.getElementById('adminSearchResults');
            const resultsContainer = document.getElementById('adminResultsContainer');

            if (!searchId) {
                alert('Please enter a user ID to search.');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'get_user_by_id');
                formData.append('user_id', searchId);

                const response = await fetch('data.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success && result.data) {
                    const user = result.data;
                    let borrowedBooksHTML = '';
                    
                    if (user.borrowing_history && user.borrowing_history.length > 0) {
                        user.borrowing_history.forEach(book => {
                            borrowedBooksHTML += `
                                <div class="borrowed-book">
                                    <strong>${book.title}</strong> by ${book.author}
                                    <br><small>Returned: ${book.date_returned}</small>
                                </div>
                            `;
                        });
                    } else {
                        borrowedBooksHTML = '<p>No borrowing history found.</p>';
                    }

                    resultsContainer.innerHTML = `
                        <div class="user-result">
                            <h4>${user.name}</h4>
                            <p><strong>ID:</strong> ${user.id}</p>
                            <p><strong>Email:</strong> ${user.email}</p>
                            <div class="borrowed-books">
                                <h5>Past Borrowed Books:</h5>
                                ${borrowedBooksHTML}
                            </div>
                        </div>
                    `;
                    resultsSection.style.display = 'block';
                } else {
                    resultsContainer.innerHTML = '<p>No user found with the provided ID.</p>';
                    resultsSection.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while searching.');
            }
        }

        // Search user by name functionality
        async function adminSearchUserByName() {
            const searchTerm = document.getElementById('adminUserNameSearch').value.toLowerCase().trim();
            const resultsSection = document.getElementById('adminSearchResults');
            const resultsContainer = document.getElementById('adminResultsContainer');

            if (!searchTerm) {
                alert('Please enter a user name to search.');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'search_users');
                formData.append('search_term', searchTerm);

                const response = await fetch('data.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    let resultsHTML = '';
                    result.data.forEach(user => {
                        resultsHTML += `
                            <div class="user-result">
                                <h4>${user.name}</h4>
                                <p><strong>ID:</strong> ${user.id}</p>
                                <p><strong>Email:</strong> ${user.email}</p>
                                <p><strong>Member Since:</strong> ${user.created_at}</p>
                            </div>
                        `;
                    });
                    resultsContainer.innerHTML = resultsHTML;
                    resultsSection.style.display = 'block';
                } else {
                    resultsContainer.innerHTML = '<p>No users found matching your search.</p>';
                    resultsSection.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while searching.');
            }
        }

        // Add book functionality
        document.getElementById('addBookForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const title = document.getElementById('bookTitle').value;
            const author = document.getElementById('bookAuthor').value;
            const isbn = document.getElementById('bookISBN').value;
            const genre = document.getElementById('bookGenre').value;

            // Here you would typically send to PHP backend
            alert(`Book "${title}" by ${author} would be added to the system.`);
            
            // Reset form
            this.reset();
        });

        // Borrow book functionality
        function borrowBook() {
            const bookId = document.getElementById('actionBookId').value;
            const userId = document.getElementById('actionUserId').value;
            
            if (!bookId || !userId) {
                alert('Please enter both Book ID and User ID.');
                return;
            }
            
            // Here you would typically send to PHP backend
            alert(`Book ID ${bookId} would be borrowed by User ID ${userId}.`);
        }

        // Return book functionality
        function returnBook() {
            const bookId = document.getElementById('actionBookId').value;
            const userId = document.getElementById('actionUserId').value;
            
            if (!bookId || !userId) {
                alert('Please enter both Book ID and User ID.');
                return;
            }
            
            // Here you would typically send to PHP backend
            alert(`Book ID ${bookId} would be returned by User ID ${userId}.`);
        }

        // Enter key functionality
        document.getElementById('userIdSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchUserById();
            }
        });

        document.getElementById('adminUserNameSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                adminSearchUserByName();
            }
        });
    </script>
</body>
</html>