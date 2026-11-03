# Campus Equipment Borrowing System

A simple PHP + MySQL OOP final project for managing school equipment and borrowing/return transactions.

## OOP Concepts
- **Encapsulation:** class attributes are private and accessed through public methods.
- **Inheritance:** `Student` and `Staff` extend the abstract `User` class.
- **Polymorphism:** `displayInfo()` is implemented differently by `Student` and `Staff`; `UserManager::makeObject()` returns the appropriate child object.
- **Abstraction:** `User` is an abstract class with abstract `getRole()` and `displayInfo()` methods.
- **Constructors:** classes initialize object data through constructors.

## Main Classes
1. User (abstract)
2. Student
3. Staff
4. Equipment
5. BorrowTransaction
6. EquipmentManager
7. UserManager
8. TransactionManager
9. Database

## Features
- Add equipment
- View/search equipment
- Delete equipment
- Add/view/delete users
- Borrow equipment
- Return equipment
- View transactions
- MySQL data storage
- Validation and error messages

## Installation using XAMPP
1. Copy this folder into `C:\xampp\htdocs\OOP_Final_Project`.
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin.
4. Import `sql/database.sql`.
5. Open `http://localhost/OOP_Final_Project/public/`.
6. Default MySQL settings are host `localhost`, username `root`, and blank password. Change `config/database.php` if your MySQL password is different.

## Project Documentation Summary
**Problem:** Schools need a simple way to keep track of equipment, borrowers, and borrowing/return transactions.

**Objective:** Create an organized OOP application that records equipment and users, manages borrowing and returns, stores data in MySQL, and demonstrates core OOP concepts.

**Main transaction:** Borrow and return equipment.
