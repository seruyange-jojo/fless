<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Transformers\TextVectorizer;
use Rubix\ML\Transformers\TfIdfTransformer;


class SkillPredictor
{
    protected $apiUrl;
    protected $classifier;
    protected $modelPath;

    public function __construct($apiUrl, $modelPath)
    {
        $this->apiUrl = $apiUrl;
        $this->modelPath = $modelPath;

        // Load the trained classifier from a persisted model file
        $this->classifier = PersistentModel::load(new Filesystem($modelPath));
    }

    public function predictProjectsForSkills($userSkills, $topProjectsCount = 5)
    {
        $projects = $this->fetchProjects();
        $preprocessedProjects = $this->preprocessProjects($projects);

        $preprocessedUserSkills = $this->preprocessUserSkills($userSkills);

        // Vectorize the text data
        $vectorizer = new TextVectorizer();
        $vectorizer->fit($preprocessedProjects);
        $preprocessedProjects = $vectorizer->transform($preprocessedProjects);

        // Transform the text data using TF-IDF
        $tfIdf = new TfIdfTransformer();
        $tfIdf->fit($preprocessedProjects);
        $preprocessedProjects = $tfIdf->transform($preprocessedProjects);

        // Predict skills for the user
        $predictedSkills = $this->classifier->predict($preprocessedUserSkills);

        // Find the most relevant projects based on predicted skills
        $matchingProjects = $this->findMatchingProjects($predictedSkills, $projects);

        // Sort the matching projects by relevance (you may need to define a relevance metric)
        // In this example, we're assuming that projects with more matching skills are more relevant
        usort($matchingProjects, function ($a, $b) {
            return count($b['matched_skills']) - count($a['matched_skills']);
        });

        // Take the top 5 most relevant projects
        $topProjects = array_slice($matchingProjects, 0, $topProjectsCount);

        return $topProjects;
    }

    protected function fetchProjects()
    {
        $response = Http::get($this->apiUrl);
        return json_decode($response->getBody(), true);
    }

    protected function preprocessProjects($projects)
    {
        $preprocessedProjects = [];

        foreach ($projects as $project) {
            $description = $project['description'] ?? ''; // Adjust this based on your project dataset structure
            $preprocessedProjects[] = $description;
        }

        return $preprocessedProjects;
    }

    protected function preprocessUserSkills($userSkills)
    {
        $userSkillsArray = explode(',', $userSkills);
        return $userSkillsArray;
    }

    protected function findMatchingProjects($predictedSkills, $projects)
    {
        $matchingProjects = [];

        foreach ($projects as $project) {
            // Analyze the project description to determine which skills are associated with it
            $descriptionSkills = $this->analyzeProjectDescription($project, $predictedSkills);

            if (!empty($descriptionSkills)) {
                $project['matched_skills'] = $descriptionSkills;
                $matchingProjects[] = $project;
            }
        }

        return $matchingProjects;
    }

    protected function analyzeProjectDescription($project, $predictedSkills)
    {
        // Implement your logic to analyze the project description and extract relevant skills
        // For example, you can use NLP techniques or keyword matching to identify skills
        // Return an array of relevant skills found in the description

        // Example: Placeholder logic that checks for predicted skills in the description
        $foundSkills = [];

        $description = $project['description'];

        foreach ($predictedSkills as $skill) {
            if (stripos($description, $skill) !== false) {
                $foundSkills[] = $skill;
            }
        }

        return $foundSkills;
    }
}
