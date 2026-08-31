# DoMedia Full Stack Developer Take-Home Test - Fixes

## Environment / Setup

The project was developed and tested using the following local environment:

- PHP 8.2.12
- XAMPP
- Apache
- MariaDB 10.4.32
- Google Chrome
- Visual Studio Code

## How to Run the Project

1. Start Apache and MySQL in XAMPP.
2. Import the database schema file:

   `app/schema.sql`

3. Check the database configuration in:

   `app/db.php`

4. Serve the `app` folder through XAMPP/Apache.
5. Open the application in the browser using the project URL.

Example URL:

`http://localhost/domedia-full-stack-take-home/`

Default database settings used in the project:

- Host: `localhost`
- Database: `workshop_app`
- Username: `root`
- Password: empty

## Part A - Bugs Fixed

### 1. JSON response handling using `JSON.parse(xhr.responseText)`

- Problem: The frontend was not correctly parsing the backend JSON response. This could result in unreadable messages or generic fallback behavior.
- Fix: The JavaScript uses `JSON.parse(xhr.responseText)` before reading `success`, `message`, and `errors` from the server response.
- Why the fix works: The browser receives a JSON string from PHP, and parsing it into a JavaScript object allows the UI to display the correct success or error message based on the server response.

### 2. Phone was incorrectly read from `$_GET` instead of POST

- Problem: The phone number field was being read from the wrong request method, which caused submitted values to be ignored or lost.
- Fix: The backend reads the submitted phone number from `$_POST['phone'] ?? ''` instead of the URL query string.
- Why the fix works: Form submissions from the browser are sent as POST data, so reading the phone value from the POST payload ensures the correct data reaches the server.

### 3. Incorrect seat validation caused by OR logic

- Problem: The seat validation logic was using an incorrect condition, which allowed or rejected invalid values incorrectly.
- Fix: Validation now checks that the submitted seat count is a valid integer and within the expected allowed range.
- Why the fix works: A seat count must be a real numeric value and must meet the business rule for valid workshop registrations. This prevents invalid values such as negative numbers, non-numeric input, or unrealistic values from being processed.

### 4. Safe POST handling with `?? ''`

- Problem: Accessing form values directly without a fallback could cause warnings or undefined value errors when a field was missing.
- Fix: Each submitted field is read with the safe pattern `$_POST['field'] ?? ''`.
- Why the fix works: If a field is missing, the code receives an empty string instead of throwing warnings or failing unexpectedly. This makes the application more reliable and easier to validate.

### 5. Name validation

- Problem: Empty or whitespace-only names were accepted.
- Fix: The backend validates the name using `trim($name)` and rejects empty values.
- Why the fix works: A participant name must contain meaningful text before registration is allowed.

### 6. Email validation

- Problem: Invalid email addresses could be submitted and stored in the database.
- Fix: The server uses `filter_var($email, FILTER_VALIDATE_EMAIL)` to verify the format.
- Why the fix works: This ensures that only properly formatted email addresses pass validation, preventing broken or fake registration data.

### 7. Workshop ID validation

- Problem: Workshop IDs were not being checked properly, allowing invalid or non-positive values.
- Fix: The server validates that the workshop ID is an integer and greater than zero.
- Why the fix works: Only valid workshop records should be accepted, reducing invalid inserts and preventing errors in later database logic.

### 8. SQL injection fixed using prepared statements and bound parameters

- Problem: User input was being inserted directly into SQL queries, creating a SQL injection risk.
- Fix: The project uses prepared statements and `mysqli_stmt_bind_param(...)` for all dynamic values in the insert and lookup logic.
- Why the fix works: The database treats input as data rather than executable SQL, preventing attackers from modifying query behavior through malicious input.

### 9. Database insert result/error handling

- Problem: The project was not handling insertion failures clearly, which could hide real database problems.
- Fix: The insert operation is executed with a prepared statement, checked for success, and exceptions are caught to return a proper error response.
- Why the fix works: If the insert fails, the application does not silently continue. It reports a backend error and rolls back the transaction.

### 10. Frontend displaying real backend messages instead of only a generic error

- Problem: The browser only displayed a vague generic error even when the backend returned a meaningful message.
- Fix: The frontend checks `data.message` and `data.errors` before displaying a fallback message.
- Why the fix works: Users see the actual reason for failure, such as a validation problem, seat shortage, or database issue, instead of an unhelpful generic error.

## Part B - Workshop Capacity Feature

The application includes a workshop capacity check to prevent overbooking.

- Workshop capacity: Each workshop has a defined capacity value stored in the `workshops` table.
- Already booked seats: The application calculates booked seats by summing the `seats` column for registrations tied to the selected workshop.
- Remaining seats: The remaining capacity is calculated as:

  `remainingSeats = capacity - bookedSeats`

- Rejection rule: If the user requests more seats than remain, registration is rejected with the response message:

  `Not enough seats available.`

Example:

- Capacity = 20
- Already booked = 19
- Requested seats = 2
- Remaining = 1
- Result: reject registration because 2 > 1

This logic ensures the application does not allow a booking that exceeds the workshop limit.

## Part C - Technical Questions

### A. What SQL injection problem existed and how prepared statements fixed it

The original issue was that dynamic form values were being inserted into SQL queries without proper parameter binding. In a vulnerable implementation, malicious input could alter the meaning of the SQL statement and potentially read, modify, or delete data.

Prepared statements fixed this by separating SQL logic from user input. The query structure is defined first, and values are bound as parameters using `mysqli_stmt_bind_param()`. This ensures user input is treated as data, not executable SQL.

### B. How concurrent registrations can cause overbooking

If two users register for the same workshop at nearly the same time, both requests may read the same booked-seat total before either insert completes. Each request may decide that enough seats remain, and both could then insert registrations. This creates a race condition and leads to overbooking.

The solution is to lock the selected workshop row during the capacity check and insert process so only one request can evaluate and reserve capacity at a time.

### C. How database failures should be reported correctly

Database failures should never be hidden behind a generic message only. The backend should return a clear JSON response, such as a `success: false` status and a descriptive `message`, while also rolling back the transaction when necessary. This makes troubleshooting easier and tells the user that the request failed for a valid reason rather than silently failing.

## Race Condition Solution

The application protects against simultaneous registrations by using a transaction and row locking with:

`SELECT capacity FROM workshops WHERE id = ? FOR UPDATE`

This is the flow:

- Begin transaction.
- Lock the selected workshop row using `FOR UPDATE`.
- Read the workshop capacity and currently booked seats.
- Check whether the requested seats exceed the remaining space.
- Insert the new registration only if capacity allows it.
- Commit the transaction if the operation succeeds.
- Roll back the transaction if any step fails.

This prevents multiple requests from reading the same seat count at the same time and then each trying to book the last remaining seats.

## Testing Performed

The following test cases were checked and marked as Passed:

| Test case                 | Expected result                                           | Status |
| ------------------------- | --------------------------------------------------------- | ------ |
| Successful registration   | Registration succeeds and message is shown                | Passed |
| Invalid email             | Validation error is returned                              | Passed |
| Seats = 0                 | Rejected as invalid                                       | Passed |
| Seats = 11                | Rejected as invalid                                       | Passed |
| 19/20 booked + request 2  | Rejected with `Not enough seats available.`               | Passed |
| 19/20 booked + request 1  | Registration succeeds                                     | Passed |
| 20/20 booked + request 1  | Rejected with `Not enough seats available.`               | Passed |
| Database persistence test | New registration row appears correctly in `registrations` | Passed |

## Main Improvements Made

- JSON parsing for backend response handling
- Correct POST handling for form input
- Safe missing-input handling with `?? ''`
- Server-side validation for name, email, workshop, and seats
- Prepared statements and bound parameters
- SQL injection protection
- Better database insert and error handling
- Capacity validation based on remaining seats
- Transaction handling for registration logic
- `FOR UPDATE` row locking
- Race-condition protection for simultaneous bookings
- Rollback handling on failure
- Clear frontend error and success messages from real backend responses

## Screen Recording

A short screen recording demonstrating the completed assignment will be added before final submission.

Recording link:

`ADD_RECORDING_LINK_HERE`
