<?php

require_once __DIR__ . '/../model/UserModel.php';
require_once __DIR__ . '/../model/ContentModel.php';

class MentorController {
    private $userModel;
    private $contentModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
        $this->contentModel = new ContentModel($db);
    }

    public function dashboard() {
        $mentor_id = 1; 

        $mentor = $this->userModel->getMentorById($mentor_id);
        if (!$mentor) {
            echo "Mentor not found or unauthorized. Please ensure a mentor with ID " . $mentor_id . " exists in your 'users' table and has user_type 'Mentor'.";
            return;
        }

        $courses = $this->contentModel->getMentorCourses($mentor_id);
        $totalCourses = $this->contentModel->getTotalCourses($mentor_id);
        $totalStudents = $this->contentModel->getTotalStudentsEnrolledForMentorCourses($mentor_id);
        $totalRevenue = $this->contentModel->getTotalRevenueForMentor($mentor_id);

        // Data yang akan dilewatkan ke view
        $data = [
            'mentor' => $mentor,
            'courses' => $courses,
            'totalCourses' => $totalCourses,
            'totalStudents' => $totalStudents,
            'totalRevenue' => $totalRevenue
        ];

        $this->loadView('Mentor/dashboard', $data);
    }

    private function loadView($viewName, $data = []) {
        extract($data);
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
?>