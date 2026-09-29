<?php

namespace App\Http\Controllers;

use App\Models\ProjectSummary;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectSummaryDocumentController extends Controller
{
  public function show(ProjectSummary $projectSummary): View
  {
    Gate::authorize('view', $projectSummary);

    $projectSummary->load('project');

    return view(
      'project-summaries.document',
      [
        'record' => $projectSummary,
      ]
    );
  }
}