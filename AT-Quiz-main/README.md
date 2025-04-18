# AT-Quiz Project

## Overview
AT-Quiz is a web-based quiz application that allows users to create, manage, and take quizzes. The application provides an intuitive interface for both administrators and users, enabling easy navigation and interaction.

## Features
- **User Authentication**: Secure login for administrators to manage quizzes and categories.
- **Quiz Creation**: Admins can create quizzes with various question types including multiple choice, true/false, and open-ended questions.
- **Category Management**: Organize quizzes into categories for better navigation.
- **Results Tracking**: View user performance and quiz results.
- **Responsive Design**: The application is designed to be mobile-friendly and accessible on various devices.

## File Structure
```
AT-Quiz-main
├── assets
│   ├── css
│   │   └── styles.css
│   ├── js
│   │   └── scripts.js
├── includes
│   ├── db.php
│   └── header.php
├── templates
│   ├── add_question_form.php
│   └── footer.php
├── dashboard.php
├── quiz_display.php
├── index.php
└── README.md
```

## Setup Instructions
1. **Clone the Repository**: 
   ```
   git clone <repository-url>
   ```
2. **Install Dependencies**: Ensure you have PHP and a web server (like XAMPP) installed.
3. **Database Configuration**: 
   - Update the `includes/db.php` file with your database credentials.
   - Create a database and import the necessary SQL files if provided.
4. **Run the Application**: 
   - Start your web server and navigate to `http://localhost/AT-Quiz-main/index.php`.

## Usage Guidelines
- **Admin Dashboard**: Access the dashboard to manage quizzes and categories.
- **Creating Questions**: Use the form in the dashboard to add questions with different input types.
- **Taking Quizzes**: Users can select quizzes from the display page and submit their answers.

## Contributing
Contributions are welcome! Please fork the repository and submit a pull request for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for details.