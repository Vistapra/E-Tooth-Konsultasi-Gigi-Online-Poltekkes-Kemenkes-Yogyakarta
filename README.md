# E-Tooth: Konsultasi Gigi Online Poltekkes Kemenkes Yogyakarta

## Overview
E-Tooth is a comprehensive online dental consultation platform developed for Poltekkes Kemenkes Yogyakarta (Health Polytechnic of the Ministry of Health in Yogyakarta). This web application aims to bridge the gap between dental professionals and patients, providing accessible dental care services and information.

## Key Features
1. **Online Consultation**: 
   - Real-time chat with qualified dentists
   - Secure file sharing for dental records and images
   - Appointment scheduling system

2. **User Dashboard**:
   - Patient profile management
   - Consultation history and records
   - Personalized dental care recommendations

3. **Dentist Portal**:
   - Professional profile management
   - Patient case management
   - Consultation scheduling and reminders

4. **Educational Resources**:
   - Dental health articles and tips
   - Interactive dental care guides
   - FAQ section on common dental issues

5. **Admin Panel**:
   - User management (patients and dentists)
   - Content management for educational resources
   - Analytics and reporting tools

6. **Search Functionality**:
   - Find dentists by specialization
   - Search dental health topics and articles

7. **Responsive Design**:
   - Seamless experience across desktop and mobile devices

## Technical Stack
- **Backend**: Laravel PHP Framework
- **Frontend**: Blade templating engine with JavaScript
- **Database**: MySQL
- **Authentication**: Laravel Breeze
- **Real-time Communication**: Laravel Websockets / Pusher
- **Styling**: Tailwind CSS

## Security Features
- Encrypted data transmission
- Secure user authentication and authorization
- GDPR-compliant data handling

## Contributing
We welcome contributions from the community. Please read our contributing guidelines before submitting pull requests.

## License
This project is licensed under the [MIT License](LICENSE.md).

## About Poltekkes Kemenkes Yogyakarta
Poltekkes Kemenkes Yogyakarta is a leading health education institution in Indonesia, committed to advancing healthcare education and services. This E-Tooth platform represents our dedication to leveraging technology for improved dental health services and education.

## Contact
For inquiries or support, please contact [insert contact information].

## Installation and Setup
## Prerequisites
Before you begin, ensure you have the following installed on your system:
- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL or MariaDB
- Git

## Step 1: Clone the Repository
```bash
git clone https://github.com/your-username/e-tooth.git](https://github.com/Vistapra/E-Tooth-Konsultasi-Gigi-Online-Poltekkes-Kemenkes-Yogyakarta.git
cd e-tooth
```

## Step 2: Install PHP Dependencies
```bash
composer install
```

## Step 3: Install JavaScript Dependencies
```bash
npm install
```

## Step 4: Set Up Environment Variables
1. Copy the `.env.example` file to create a new `.env` file:
   ```bash
   cp .env.example .env
   ```
2. Open the `.env` file and update the following variables:
   - Set your database credentials:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=e_tooth
     DB_USERNAME=your_database_username
     DB_PASSWORD=your_database_password
     ```
   - Set your application key:
     ```bash
     php artisan key:generate
     ```

## Step 5: Set Up the Database
1. Create a new MySQL database for E-Tooth.
2. Run database migrations:
   ```bash
   php artisan migrate
   ```
3. (Optional) Seed the database with sample data:
   ```bash
   php artisan db:seed
   ```

## Step 6: Set Up File Storage
1. Create a symbolic link for public file storage:
   ```bash
   php artisan storage:link
   ```

## Step 7: Configure Pusher for Real-time Features (if applicable)
1. Sign up for a Pusher account at https://pusher.com/
2. Create a new Pusher app and note down the app credentials
3. Update your `.env` file with Pusher credentials:
   ```
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_APP_CLUSTER=your_app_cluster
   ```

## Step 8: Compile Assets
```bash
npm run dev
```
For production:
```bash
npm run build
```

## Step 9: Start the Development Server
```bash
php artisan serve
```

The application should now be running at `http://localhost:8000`.

## Additional Configuration

### Setting Up Email
1. Update the `.env` file with your email service credentials:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=your_smtp_host
   MAIL_PORT=your_smtp_port
   MAIL_USERNAME=your_email_username
   MAIL_PASSWORD=your_email_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your_from_email
   ```

### Configuring Queue Worker (for background jobs)
1. Set up a queue driver in your `.env` file (e.g., database or Redis)
2. Run the queue worker:
   ```bash
   php artisan queue:work
   ```

## Troubleshooting
- If you encounter any issues with file permissions, run:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```
- For any composer-related issues, try:
  ```bash
  composer dump-autoload
  ```

## Running Tests
To run the test suite:
```bash
php artisan test
```

## Additional Notes
- Make sure to configure your local development environment to match the production environment as closely as possible.
- Regularly pull updates from the main repository to keep your local copy up to date.

For any additional help or inquiries, please refer to the project documentation or contact the development team.

Empowering dental health through technology - E-Tooth, your trusted online dental consultation platform.
