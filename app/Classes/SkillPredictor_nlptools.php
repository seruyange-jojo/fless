<?php
namespace App\Classes;

use Illuminate\Support\Facades\Http;
use NlpTools\Tokenizers\WhitespaceTokenizer;
use NlpTools\Stemmers\PorterStemmer;
use Phpml\Classification\SVC;

class SkillPredictor
{
    protected $apiUrl;
    protected $tokenizer;
    protected $stemmer;
    protected $classifier;

    protected $skillsCorpus;
    

    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        $this->tokenizer = new WhitespaceTokenizer();
        $this->stemmer = new PorterStemmer();
        $this->classifier = new SVC();
        $this->skillsCorpus  = [
            "programming",
            "web development",
            "mobile app development",
            "data analysis",
            "machine learning",
            "artificial intelligence",
            "database management",
            "network security",
            "cloud computing",
            "DevOps",
            "system administration",
            "front-end development",
            "back-end development",
            "full-stack development",
            "UI/UX design",
            "project management",
            "content writing",
            "marketing",
            "social media management",
            "financial analysis",
            "accounting",
            "electrical engineering",
            "mechanical engineering",
            "civil engineering",
            "chemical engineering",
            "data science",
            "biology",
            "chemistry",
            "physics",
            "mathematics",
            "geology",
            "environmental science",
            "astronomy",
            "medicine",
            "nursing",
            "pharmacy",
            "veterinary medicine",
            "psychology",
            "education",
            "law",
            "business management",
            "entrepreneurship",
            "sales",
            "customer service",
            "human resources",
            "graphic design",
            "video editing",
            "music production",
            "photography",
            "animation",
            "sports",
            "cooking",
            "travel",
            "foreign languages",
            "history",
            "literature",
            "philosophy",
            "art",
            "fashion",
            "gardening",
            "DIY projects",
            "yoga",
            "fitness",
            "gaming",
            "film",
            "sustainability",
            "volunteering",
            "leadership",
            "communication skills",
            "problem-solving",
        ];
    }

    public function predictProjectsForSkills($userSkills, $topProjectsCount = 5)
    {
        $projects = $this->fetchProjects();
        $preprocessedProjects = $this->preprocessProjects($projects);
        $featureVectors = $this->extractFeatures($preprocessedProjects);
        $labels = $this->extractLabels($projects);

        $this->trainClassifier($featureVectors, $labels);

        $preprocessedUserSkills = $this->preprocessUserSkills($userSkills);
        $featureVector = $this->extractUserSkillsFeature($preprocessedUserSkills);

        // Predict skills for the user
        $predictedSkills = $this->classifier->predict([$featureVector]);

        // Find the most relevant projects based on predicted skills
        $matchingProjects = $this->findMatchingProjects($predictedSkills[0], $preprocessedProjects);

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
            $preprocessedProjects[] = $this->preprocessProject($description);
        }

        return $preprocessedProjects;
    }

    protected function preprocessProject($project)
    {
        $tokens = $this->tokenizer->tokenize($project);
        $stemmedTokens = array_map(function ($token) {
            return $this->stemmer->stem($token);
        }, $tokens);

        return $stemmedTokens;
    }

    protected function extractFeatures($preprocessedProjects)
    {
        $featureVectors = [];

        foreach ($preprocessedProjects as $project) {
            $featureVector = [];

            foreach ($this->skillsCorpus as $skill) {
                $featureVector[] = in_array($skill, $project) ? 1 : 0;
            }

            $featureVectors[] = $featureVector;
        }

        return $featureVectors;
    }

    protected function extractLabels($projects)
    {
        $labels = [];

        foreach ($projects as $project) {
            if (isset($project['skills'])) {
                $labels[] = $project['skills'];
            } else {
                // Handle the case where 'skills' key is missing or undefined
                // You can log an error, skip the project, or handle it as needed
                // For example, you can assign an empty array as a label
                $labels[] = [];
            }
        }

        return $labels;
    }

    protected function trainClassifier($featureVectors, $labels)
    {
        // $this->classifier->train($featureVectors, $labels);
        // Convert elements in the $labels array to strings
        $labels = array_map('strval', $labels);

        // Train the classifier
        $this->classifier->train($featureVectors, $labels);

    }

    protected function preprocessUserSkills($userSkills)
    {
        $userSkillsArray = explode(',', $userSkills);
        $preprocessedUserSkills = [];

        foreach ($userSkillsArray as $skill) {
            $preprocessedUserSkills[] = $this->stemmer->stem(trim($skill));
        }

        return $preprocessedUserSkills;
    }

    protected function extractUserSkillsFeature($preprocessedUserSkills)
    {
        $featureVector = [];

        foreach ($this->skillsCorpus as $skill) {
            $featureVector[] = in_array($skill, $preprocessedUserSkills) ? 1 : 0;
        }

        return $featureVector;
    }

    protected function findMatchingProjects($predictedSkills, $projects)
    {
        $matchingProjects = [];

        foreach ($projects as $project) {
            $matchedSkills = array_intersect($predictedSkills, $project);
            if (!empty($matchedSkills)) {
                $project['matched_skills'] = $matchedSkills;
                $matchingProjects[] = $project;
            }
        }

        return $matchingProjects;
    }
}
// controlller classs for the above codebase

// // receives the user input and processes it to get the skills provifing them to a view function

// $apiUrl = 'https://data.nasa.gov/resource/bq5k-hbdz.json';
// $skillsCorpus = explode(',',$request->skills);

// // Get user skills from the request (assuming they are passed as a comma-separated string)
// $userSkills = $request->input('user_skills');

// // Instantiate the SkillPredictor class
// $skillPredictor = new SkillPredictor($apiUrl, $skillsCorpus); // Replace with your actual API URL and skills corpus

// // Call the predictProjectsForSkills method to get the top 5 projects
// $topProjects = $skillPredictor->predictProjectsForSkills($userSkills, 5);
// dd($topProjects);
// // return view('top_projects', ['topProjects' => $topProjects]);