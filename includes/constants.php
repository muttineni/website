<?php
/*******************************************************************************
** define event type constants
*/
define('USERS_CREATE',                        1); // USERS: Create User
define('USERS_UPDATE',                        2); // USERS: Update User Details
define('USERS_DELETE',                        3); // USERS: Delete User
define('USERS_ADD_COURSE',                    4); // USERS: Add Course
define('USERS_REMOVE_COURSE',                 5); // USERS: Remove Course
define('USERS_ADD_TO_PARTNER',               66); // USERS: Add to Partner as Diocesan Partner Representative
define('USERS_REMOVE_FROM_PARTNER',          67); // USERS: Remove from Partner as Diocesan Partner Representative
define('USERS_ADD_CERT_PROG',                78); // USERS: Add Certificate Program
define('USERS_REMOVE_CERT_PROG',             79); // USERS: Remove Certificate Program
define('USERS_COURSES_CREATE',                6); // USERS_COURSES: Create Course Registration
define('USERS_COURSES_UPDATE',                7); // USERS_COURSES: Update Course Registration Details
define('USERS_COURSES_DELETE',                8); // USERS_COURSES: Delete Course Registration
define('CREDITS_UPDATE',                      9); // CREDITS: Update Credit Amount for User
define('COURSES_CREATE',                     10); // COURSES: Create Course
define('COURSES_UPDATE',                     11); // COURSES: Update Course Details
define('COURSES_DELETE',                     12); // COURSES: Delete Course
define('COURSES_ADD_USER',                   13); // COURSES: Add User
define('COURSES_REMOVE_USER',                14); // COURSES: Remove User
define('CYCLES_CREATE',                      15); // CYCLES: Create Cycle
define('CYCLES_UPDATE',                      16); // CYCLES: Update Cycle Details
define('CYCLES_DELETE',                      17); // CYCLES: Delete Cycle
define('CYCLES_ADD_COURSE',                  18); // CYCLES: Add Course
define('CYCLES_REMOVE_COURSE',               19); // CYCLES: Remove Course
define('COURSE_SUBJECTS_CREATE',             20); // COURSE_SUBJECTS: Create Course Subject
define('COURSE_SUBJECTS_UPDATE',             21); // COURSE_SUBJECTS: Update Course Subject Details
define('COURSE_SUBJECTS_DELETE',             22); // COURSE_SUBJECTS: Delete Course Subject
define('COURSE_SUBJECTS_ADD_SESSION',        23); // COURSE_SUBJECTS: Add Session
define('COURSE_SUBJECTS_UPDATE_SESSION',     24); // COURSE_SUBJECTS: Update Session
define('COURSE_SUBJECTS_REMOVE_SESSION',     25); // COURSE_SUBJECTS: Remove Session
define('COURSE_SUBJECTS_ADD_RESOURCE',       26); // COURSE_SUBJECTS: Add Resource
define('COURSE_SUBJECTS_UPDATE_RESOURCE',    27); // COURSE_SUBJECTS: Update Resource
define('COURSE_SUBJECTS_REMOVE_RESOURCE',    28); // COURSE_SUBJECTS: Remove Resource
define('ORDERS_CREATE',                      29); // ORDERS: Create Order
define('ORDERS_UPDATE',                      30); // ORDERS: Update Order Details
define('ORDERS_DELETE',                      31); // ORDERS: Delete Order
define('ORDERS_ADD_TRANSACTION',             32); // ORDERS: Add Transaction to Order
define('ORDERS_REMOVE_TRANSACTION',          33); // ORDERS: Remove Transaction from Order
define('ORDERS_UPDATE_ORDER_STATUS',         34); // ORDERS: Update Order Status
define('ORDERS_UPDATE_AMOUNT_PAID',          35); // ORDERS: Update Amount Paid
define('ORDERS_UPDATE_DISCOUNT_TYPE',        36); // ORDERS: Update Discount Type
define('ORDERS_TRANSACTIONS_CREATE',         37); // ORDERS_TRANSACTIONS: Create Order-Transaction Record
define('ORDERS_TRANSACTIONS_UPDATE',         38); // ORDERS_TRANSACTIONS: Update Order-Transaction Record
define('ORDERS_TRANSACTIONS_DELETE',         39); // ORDERS_TRANSACTIONS: Delete Order-Transaction Record
define('ORDERS_TRANSACTIONS_ISSUE_CREDIT',   40); // ORDERS_TRANSACTIONS: Issue Credit
define('ORDERS_TRANSACTIONS_REVERSE_CREDIT', 41); // ORDERS_TRANSACTIONS: Reverse Credit
define('ORDERS_TRANSACTIONS_ISSUE_REFUND',   42); // ORDERS_TRANSACTIONS: Issue Refund
define('ORDERS_TRANSACTIONS_REVERSE_REFUND', 43); // ORDERS_TRANSACTIONS: Reverse Refund
define('TRANSACTIONS_CREATE',                44); // TRANSACTIONS: Create Transaction
define('TRANSACTIONS_UPDATE',                45); // TRANSACTIONS: Update Transaction Details
define('TRANSACTIONS_DELETE',                46); // TRANSACTIONS: Delete Transaction
define('TRANSACTIONS_ADD_ORDER',             47); // TRANSACTIONS: Add Order to Transaction
define('TRANSACTIONS_REMOVE_ORDER',          48); // TRANSACTIONS: Remove Order from Transaction
define('TRANSACTIONS_UPDATE_STATUS_CMS',     49); // TRANSACTIONS: Update Status (CMS)
define('TRANSACTIONS_UPDATE_STATUS_PMT',     50); // TRANSACTIONS: Update Status (Online Payment System)
define('TRANSACTIONS_UPDATE_STATUS_RPT',     51); // TRANSACTIONS: Update Status (Bursar Report Upload)
define('PAYMENT_CODES_CREATE',               52); // PAYMENT_CODES: Create Payment Code
define('PAYMENT_CODES_UPDATE',               53); // PAYMENT_CODES: Update Payment Code Details
define('PAYMENT_CODES_DELETE',               54); // PAYMENT_CODES: Delete Payment Code
define('PARTNERS_CREATE',                    55); // PARTNERS: Create Partner
define('PARTNERS_UPDATE',                    56); // PARTNERS: Update Partner Details
define('PARTNERS_DELETE',                    57); // PARTNERS: Delete Partner
define('PARTNERS_ADD_REP',                   64); // PARTNERS: Add Diocesan Partner Representative
define('PARTNERS_REMOVE_REP',                65); // PARTNERS: Remove Diocesan Partner Representative
define('RESOURCES_CREATE',                   58); // RESOURCES: Create Resource
define('RESOURCES_UPDATE',                   59); // RESOURCES: Update Resource Details
define('RESOURCES_DELETE',                   60); // RESOURCES: Delete Resource
define('SESSIONS_CREATE',                    61); // SESSIONS: Create Session
define('SESSIONS_UPDATE',                    62); // SESSIONS: Update Session Details
define('SESSIONS_DELETE',                    63); // SESSIONS: Delete Session
define('CERT_PROGS_CREATE',                  68); // CERT_PROGS: Create Certificate Program
define('CERT_PROGS_UPDATE',                  69); // CERT_PROGS: Update Certificate Program Details
define('CERT_PROGS_DELETE',                  70); // CERT_PROGS: Delete Certificate Program
define('CERT_PROGS_ADD_COURSE',              71); // CERT_PROGS: Add Course
define('CERT_PROGS_REMOVE_COURSE',           72); // CERT_PROGS: Remove Course
define('CERT_PROGS_ADD_USER',                73); // CERT_PROGS: Add User
define('CERT_PROGS_REMOVE_USER',             74); // CERT_PROGS: Remove User
define('CERTS_USERS_CREATE',                 75); // CERTS_USERS: Create Certificate Program Registration
define('CERTS_USERS_UPDATE',                 76); // CERTS_USERS: Update Certificate Program Registration
define('CERTS_USERS_DELETE',                 77); // CERTS_USERS: Delete Certificate Program Registration
define('CERT_PROG_REQS_ADD_REQ',             80); // CERT_PROG_REQS: Add Certificate Program Requirement
define('CERT_PROG_REQS_UPDATE_REQ',          81); // CERT_PROG_REQS: Update Certificate Program Requirement
define('CERT_PROG_REQS_REMOVE_REQ',          82); // CERT_PROG_REQS: Remove Certificate Program Requirement
?>
