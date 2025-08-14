// Sample data for the library system
const users = [
    { 
        id: 'U001', 
        name: 'John Smith', 
        email: 'john.smith@stanford.edu',
        borrowedBooks: [
            { title: 'The Great Gatsby', author: 'F. Scott Fitzgerald', dateReturned: '2024-12-15' },
            { title: 'To Kill a Mockingbird', author: 'Harper Lee', dateReturned: '2024-11-20' },
            { title: '1984', author: 'George Orwell', dateReturned: '2025-01-10' }
        ]
    },
    { 
        id: 'U002', 
        name: 'Emily Johnson', 
        email: 'emily.johnson@stanford.edu',
        borrowedBooks: [
            { title: 'Pride and Prejudice', author: 'Jane Austen', dateReturned: '2024-10-30' },
            { title: 'The Catcher in the Rye', author: 'J.D. Salinger', dateReturned: '2025-01-05' }
        ]
    },
    { 
        id: 'U003', 
        name: 'Michael Brown', 
        email: 'michael.brown@stanford.edu',
        borrowedBooks: [
            { title: 'Lord of the Flies', author: 'William Golding', dateReturned: '2024-12-01' },
            { title: 'Brave New World', author: 'Aldous Huxley', dateReturned: '2024-11-15' }
        ]
    },
    { 
        id: 'U004', 
        name: 'Sarah Davis', 
        email: 'sarah.davis@stanford.edu',
        borrowedBooks: [
            { title: 'The Hobbit', author: 'J.R.R. Tolkien', dateReturned: '2025-01-12' }
        ]
    },
    { 
        id: 'U005', 
        name: 'David Wilson', 
        email: 'david.wilson@stanford.edu',
        borrowedBooks: [
            { title: 'Animal Farm', author: 'George Orwell', dateReturned: '2024-11-25' },
            { title: 'Of Mice and Men', author: 'John Steinbeck', dateReturned: '2024-12-20' }
        ]
    }
];

const availableBooks = [
    { title: 'The Adventures of Tom Sawyer', author: 'Mark Twain', isbn: '978-0486400778', genre: 'Classic Fiction' },
    { title: 'Jane Eyre', author: 'Charlotte Brontë', isbn: '978-0486424491', genre: 'Classic Romance' },
    { title: 'Moby Dick', author: 'Herman Melville', isbn: '978-0486430943', genre: 'Adventure' },
    { title: 'The Picture of Dorian Gray', author: 'Oscar Wilde', isbn: '978-0486278070', genre: 'Gothic Fiction' },
    { title: 'Wuthering Heights', author: 'Emily Brontë', isbn: '978-0486290567', genre: 'Gothic Romance' },
    { title: 'Little Women', author: 'Louisa May Alcott', isbn: '978-0486424330', genre: 'Coming-of-age' },
    { title: 'The Count of Monte Cristo', author: 'Alexandre Dumas', isbn: '978-0486456430', genre: 'Adventure' },
    { title: 'Frankenstein', author: 'Mary Shelley', isbn: '978-0486282114', genre: 'Gothic Horror' },
    { title: 'Dracula', author: 'Bram Stoker', isbn: '978-0486411095', genre: 'Horror' },
    { title: 'The Strange Case of Dr. Jekyll and Mr. Hyde', author: 'Robert Louis Stevenson', isbn: '978-0486266886', genre: 'Psychological Horror' },
    { title: 'The Time Machine', author: 'H.G. Wells', isbn: '978-0486284729', genre: 'Science Fiction' },
    { title: 'The War of the Worlds', author: 'H.G. Wells', isbn: '978-0486295060', genre: 'Science Fiction' }
];

const unavailableBooks = [
    { title: 'The Great Gatsby', author: 'F. Scott Fitzgerald', isbn: '978-0486280608', genre: 'Classic Fiction', borrower: 'Alice Wilson', dueDate: '2025-09-15' },
    { title: 'To Kill a Mockingbird', author: 'Harper Lee', isbn: '978-0486424323', genre: 'Drama', borrower: 'Bob Anderson', dueDate: '2025-09-20' },
    { title: '1984', author: 'George Orwell', isbn: '978-0486424361', genre: 'Dystopian Fiction', borrower: 'Carol Martinez', dueDate: '2025-08-25' },
    { title: 'Pride and Prejudice', author: 'Jane Austen', isbn: '978-0486284736', genre: 'Romance', borrower: 'David Thompson', dueDate: '2025-09-10' },
    { title: 'The Catcher in the Rye', author: 'J.D. Salinger', isbn: '978-0316769174', genre: 'Coming-of-age', borrower: 'Eva Rodriguez', dueDate: '2025-08-30' },
    { title: 'Lord of the Flies', author: 'William Golding', isbn: '978-0486424248', genre: 'Dystopian Fiction', borrower: 'Frank Lee', dueDate: '2025-09-05' },
    { title: 'Brave New World', author: 'Aldous Huxley', isbn: '978-0486472454', genre: 'Dystopian Fiction', borrower: 'Grace Kim', dueDate: '2025-09-12' },
    { title: 'The Hobbit', author: 'J.R.R. Tolkien', isbn: '978-0547928227', genre: 'Fantasy', borrower: 'Henry Chen', dueDate: '2025-08-28' }
];

// Navigation functionality
function showPage(pageId) {
    // Hide all pages
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => {
        page.classList.remove('active');
    });

    // Remove active class from all nav buttons
    const navBtns = document.querySelectorAll('.nav-btn');
    navBtns.forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected page
    const selectedPage = document.getElementById(pageId);
    if (selectedPage) {
        selectedPage.classList.add('active');
    }

    // Add active class to clicked nav button
    if (event && event.target) {
        event.target.classList.add('active');
    }

    // Load page-specific content
    if (pageId === 'available') {
        loadAvailableBooks();
    } else if (pageId === 'unavailable') {
        loadUnavailableBooks();
    }
}

// Search user by name functionality
function searchUserByName() {
    const searchTerm = document.getElementById('userNameSearch').value.toLowerCase().trim();
    const resultsSection = document.getElementById('userSearchResults');
    const resultsContainer = document.getElementById('userResultsContainer');

    if (!searchTerm) {
        alert('Please enter a user name to search.');
        return;
    }

    const matchingUsers = users.filter(user => 
        user.name.toLowerCase().includes(searchTerm)
    );

    if (matchingUsers.length > 0) {
        let resultsHTML = '';
        matchingUsers.forEach(user => {
            const currentBooks = Math.floor(Math.random() * 3) + 1;
            resultsHTML += `
                <div class="user-result">
                    <h4>${user.name}</h4>
                    <p><strong>ID:</strong> ${user.id}</p>
                    <p><strong>Email:</strong> ${user.email}</p>
                    <p><strong>Books Currently Reading:</strong> ${currentBooks}</p>
                    <p><strong>Total Books Borrowed:</strong> ${user.borrowedBooks.length}</p>
                </div>
            `;
        });
        resultsContainer.innerHTML = resultsHTML;
        resultsSection.style.display = 'block';
    } else {
        resultsContainer.innerHTML = '<p>No users found matching your search.</p>';
        resultsSection.style.display = 'block';
    }
}

// Load available books
function loadAvailableBooks() {
    const grid = document.getElementById('availableBooksGrid');
    if (!grid) return;

    let booksHTML = '';

    availableBooks.forEach(book => {
        booksHTML += `
            <div class="book-card">
                <h3>${book.title}</h3>
                <p><strong>Author:</strong> ${book.author}</p>
                <p><strong>ISBN:</strong> ${book.isbn}</p>
                <p><strong>Genre:</strong> ${book.genre}</p>
                <span class="book-status available">Available</span>
            </div>
        `;
    });

    grid.innerHTML = booksHTML;
}

// Load unavailable books
function loadUnavailableBooks() {
    const grid = document.getElementById('unavailableBooksGrid');
    if (!grid) return;

    let booksHTML = '';

    unavailableBooks.forEach(book => {
        booksHTML += `
            <div class="book-card">
                <h3>${book.title}</h3>
                <p><strong>Author:</strong> ${book.author}</p>
                <p><strong>ISBN:</strong> ${book.isbn}</p>
                <p><strong>Genre:</strong> ${book.genre}</p>
                <p><strong>Borrowed by:</strong> ${book.borrower}</p>
                <p><strong>Due Date:</strong> ${book.dueDate}</p>
                <span class="book-status unavailable">Borrowed</span>
            </div>
        `;
    });

    grid.innerHTML = booksHTML;
}

// Utility function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Enter key functionality for search inputs
document.addEventListener('DOMContentLoaded', function() {
    const userNameSearch = document.getElementById('userNameSearch');
    
    if (userNameSearch) {
        userNameSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchUserByName();
            }
        });
    }

    // Initialize the page with content
    loadAvailableBooks();
    loadUnavailableBooks();
});

// Export functions for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        users,
        availableBooks,
        unavailableBooks,
        showPage,
        searchUserByName,
        loadAvailableBooks,
        loadUnavailableBooks
    };
}