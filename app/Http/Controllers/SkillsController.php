<?php

namespace App\Http\Controllers;

use App\Classes\SkillPredictor;
use Illuminate\Http\Request;

class SkillsController extends Controller
{
    
    public function create()
    {
        // here the user inputs data
        return view('skillz');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function process(Request $request)
    {
        $apiUrl = 'https://data.nasa.gov/resource/bq5k-hbdz.json'; // Replace with your API URL
        $modelPath = storage_path('models/skill_predictor.model'); // Replace with your model file path

        $userSkills = $request->input('user_skills');

        // Create a skill predictor instance
        $skillPredictor = new SkillPredictor($apiUrl, $modelPath);

        // Predict and retrieve the top 5 matching projects
        $topProjects = $skillPredictor->predictProjectsForSkills($userSkills, 5);

        // You can return the top projects as a response or pass them to a view
        // return view('projects', compact('topProjects'));

        dd($topProjects);
    }

}
