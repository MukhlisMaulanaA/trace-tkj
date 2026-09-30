<?php

namespace App\Http\Controllers;

use App\Exports\ProjectSummaryExport;
use App\Models\ProjectSummary;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectSummaryDocumentController extends Controller
{
  public function show(ProjectSummary $projectSummary): BinaryFileResponse
  {
    Gate::authorize('view', $projectSummary);

    $export = new ProjectSummaryExport($projectSummary);
    $temporaryFile = $export->download();

    return response()->download(
      $temporaryFile,
      $export->filename(),
      ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    )->deleteFileAfterSend(true);
  }
}