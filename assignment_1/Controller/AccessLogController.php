<?php
class AccessLogController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function displayLogs() {
        session_start();

        // Capture IP address and requested URL
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $requested_url = $_SERVER['REQUEST_URI'];
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

	

        // Check if user is logged in
        if (!$username) {
            $this->model->logPageAccess($ip_address, $requested_url, null, 0); // Log denied access
            header("Location: login_page.php");
            exit();
        }

        $user = $this->model->getUserRole($username);

        // Check if the logged-in user is an admin or moderator
        if ($user['role'] != 'admin' && $user['role'] != 'moderator') {
	    header('HTTP/1.0 403 Forbidden');
           // Display an access denied message
    	    echo "HTTP/1.0 403 Forbidden <br>"; 
            echo "Access denied. You do not have permission to view this page.";
            $this->model->logPageAccess($ip_address, $requested_url, $username, 0); // Log denied access
            exit();
        }

        // Log allowed access
        $this->model->logPageAccess($ip_address, $requested_url, $username, 1);

        // Fetch logs
        $ip_filter = isset($_GET['ip']) ? $_GET['ip'] : '';
        $logs = $this->model->getLogs($ip_filter);

        // Display logs in various formats
        AccessLogView::displayLogsTable($logs);
        AccessLogView::displayLogsList($logs);
        AccessLogView::displayLogsJSON($logs);
        AccessLogView::displayLogsXML($logs);
    }
}
?>
