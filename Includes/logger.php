<?php
class Logger {
    private $logFile;
    private $logLevel;
    
    // Log levels
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    
    public function __construct($logFile = 'logs/logger.txt', $logLevel = self::INFO) {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        // Create log file if it doesn't exist
        if (!file_exists($logFile)) {
            touch($logFile);
            chmod($logFile, 0666);
        }
    }
    
    /**
     * Write a log message to the log file
     * 
     * @param string $message The message to log
     * @param string $level The log level (ERROR, WARNING, INFO, DEBUG)
     * @param array $context Additional context data to log
     * @return bool True if the log was written successfully, false otherwise
     */
    public function log($message, $level = self::INFO, $context = array()) {
        // Check if the log level is enabled
        if (!$this->isLogLevelEnabled($level)) {
            return false;
        }
        
        // Format the log entry
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = sprintf(
            "[%s] [%s] %s %s\n",
            $timestamp,
            $level,
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        // Write to log file
        try {
            if (file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX) === false) {
                error_log("Failed to write to log file: {$this->logFile}");
                return false;
            }
            return true;
        } catch (Exception $e) {
            error_log("Error writing to log file: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log an error message
     */
    public function error($message, $context = array()) {
        return $this->log($message, self::ERROR, $context);
    }
    
    /**
     * Log a warning message
     */
    public function warning($message, $context = array()) {
        return $this->log($message, self::WARNING, $context);
    }
    
    /**
     * Log an info message
     */
    public function info($message, $context = array()) {
        return $this->log($message, self::INFO, $context);
    }
    
    /**
     * Log a debug message
     */
    public function debug($message, $context = array()) {
        return $this->log($message, self::DEBUG, $context);
    }
    
    /**
     * Check if a log level is enabled based on the current log level setting
     */
    private function isLogLevelEnabled($level) {
        $levels = array(
            self::DEBUG => 0,
            self::INFO => 1,
            self::WARNING => 2,
            self::ERROR => 3
        );
        
        return $levels[$level] >= $levels[$this->logLevel];
    }
    
    /**
     * Get the current log file path
     */
    public function getLogFile() {
        return $this->logFile;
    }
    
    /**
     * Set a new log file path
     */
    public function setLogFile($logFile) {
        $this->logFile = $logFile;
    }
    
    /**
     * Get the current log level
     */
    public function getLogLevel() {
        return $this->logLevel;
    }
    
    /**
     * Set a new log level
     */
    public function setLogLevel($logLevel) {
        if (in_array($logLevel, array(self::DEBUG, self::INFO, self::WARNING, self::ERROR))) {
            $this->logLevel = $logLevel;
            return true;
        }
        return false;
    }
}

// Create a global logger instance
$logger = new Logger();
?> 