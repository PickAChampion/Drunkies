<?php
// Development error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_message = date('[Y-m-d H:i:s] ') . "Error: [$errno] $errstr in $errfile on line $errline\n";
    error_log($error_message, 3, "error.log");
    
    if (ini_get('display_errors')) {
        echo "<div style='color: red; background-color: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid #ff9999;'>";
        echo "<strong>Error:</strong> " . htmlspecialchars($errstr);
        echo "<br><strong>File:</strong> " . htmlspecialchars($errfile);
        echo "<br><strong>Line:</strong> " . $errline;
        echo "</div>";
    }
    
    return true;
}

set_error_handler("customErrorHandler");

// Custom exception handler
function customExceptionHandler($exception) {
    $error_message = date('[Y-m-d H:i:s] ') . "Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    error_log($error_message, 3, "error.log");
    
    if (ini_get('display_errors')) {
        echo "<div style='color: red; background-color: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid #ff9999;'>";
        echo "<strong>Exception:</strong> " . htmlspecialchars($exception->getMessage());
        echo "<br><strong>File:</strong> " . htmlspecialchars($exception->getFile());
        echo "<br><strong>Line:</strong> " . $exception->getLine();
        echo "</div>";
    }
}

set_exception_handler("customExceptionHandler");
?> 