<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY SESSION MODEL
// ============================================================================

require_once __DIR__ . '/../config/db_config.php';

class StudySessionModel {
    private $conn;

    public function __construct($dbConnection = null) {
        global $conn;
        $this->conn = $dbConnection ?? $conn;
    }

    public function getUpcomingSessions($limit = 3) {
        if (!$this->conn || $this->conn->connect_error) {
            return $this->getFallbackSessions();
        }

        $query = "SELECT id, title, faculty, session_date, location_details, capacity, enrolled_count, status
                  FROM study_sessions
                  WHERE status = 'Scheduled' AND session_date >= NOW()
                  ORDER BY session_date ASC
                  LIMIT ?";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) return $this->getFallbackSessions();

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $dt = new DateTime($row['session_date']);
            $row['day'] = $dt->format('d');
            $row['month'] = strtoupper($dt->format('M'));
            $row['time_formatted'] = $dt->format('g:i A');
            $sessions[] = $row;
        }

        return !empty($sessions) ? $sessions : $this->getFallbackSessions();
    }

    public function getFallbackSessions() {
        return [
            [
                'id' => 1,
                'day' => '24',
                'month' => 'MAY',
                'faculty' => 'Computing',
                'title' => 'Algorithms: Final Exam Problem Solving',
                'time_details' => '6:30 PM - 8:00 PM',
                'enrolled_count' => 18,
                'capacity' => 24,
                'is_featured' => true
            ],
            [
                'id' => 2,
                'day' => '26',
                'month' => 'MAY',
                'faculty' => 'Business',
                'title' => 'Marketing Strategy Case Review',
                'time_details' => '4:00 PM · Online session',
                'enrolled_count' => 14,
                'capacity' => 20,
                'is_featured' => false
            ],
            [
                'id' => 3,
                'day' => '28',
                'month' => 'MAY',
                'faculty' => 'Science',
                'title' => 'Laboratory Report Review',
                'time_details' => '5:30 PM · Science laboratory',
                'enrolled_count' => 12,
                'capacity' => 16,
                'is_featured' => false
            ]
        ];
    }
}
?>

