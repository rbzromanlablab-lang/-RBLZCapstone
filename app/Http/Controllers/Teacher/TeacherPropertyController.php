<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Property;
use App\Models\PropertyRequestRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeacherPropertyController extends Controller
{
    public function dashboard(Request $request): View
    {
        $teacher = $request->user();

        $assignedPropertiesCount = Property::query()
            ->whereHas('assignments', function ($query) use ($teacher) {
                $query
                    ->where('teacher_id', $teacher->id)
                    ->where('status', Assignment::STATUS_ACTIVE);
            })
            ->count();

        $recentAssignments = Assignment::query()
            ->with('property')
            ->where('teacher_id', $teacher->id)
            ->where('status', Assignment::STATUS_ACTIVE)
            ->latest('date_assigned')
            ->take(5)
            ->get();

        $propertyRequestCount = PropertyRequestRecord::query()
            ->where('requested_by', $teacher->id)
            ->count();

        $pendingPropertyRequestCount = PropertyRequestRecord::query()
            ->where('requested_by', $teacher->id)
            ->where('status', PropertyRequestRecord::STATUS_PENDING)
            ->count();

        $recentPropertyRequests = PropertyRequestRecord::query()
            ->where('requested_by', $teacher->id)
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.dashboard', [
            'assignedPropertiesCount' => $assignedPropertiesCount,
            'recentAssignments' => $recentAssignments,
            'propertyRequestCount' => $propertyRequestCount,
            'pendingPropertyRequestCount' => $pendingPropertyRequestCount,
            'recentPropertyRequests' => $recentPropertyRequests,
        ]);
    }

    public function index(Request $request): View
    {
        $teacher = $request->user();

        $properties = Property::query()
            ->with([
                'assignments' => function ($query) use ($teacher) {
                    $query
                        ->with('propertyUnits')
                        ->where('teacher_id', $teacher->id)
                        ->where('status', Assignment::STATUS_ACTIVE)
                        ->latest('date_assigned');
                },
                'activeAssignments',
            ])
            ->whereHas('assignments', function ($query) use ($teacher) {
                $query
                    ->where('teacher_id', $teacher->id)
                    ->where('status', Assignment::STATUS_ACTIVE);
            })
            ->latest()
            ->paginate(10);

        return view('teacher.properties.index', [
            'properties' => $properties,
        ]);
    }

    public function print(Request $request): View
    {
        $teacher = $request->user();

        $properties = Property::query()
            ->with([
                'assignments' => function ($query) use ($teacher) {
                    $query
                        ->with(['assignedBy', 'propertyUnits'])
                        ->where('teacher_id', $teacher->id)
                        ->where('status', Assignment::STATUS_ACTIVE)
                        ->latest('date_assigned');
                },
                'activeAssignments',
            ])
            ->whereHas('assignments', function ($query) use ($teacher) {
                $query
                    ->where('teacher_id', $teacher->id)
                    ->where('status', Assignment::STATUS_ACTIVE);
            })
            ->orderBy('property_name')
            ->get();

        return view('teacher.properties.print', [
            'properties' => $properties,
            'teacher' => $teacher,
            'printedAt' => now(),
        ]);
    }

    public function show(Request $request, Property $property): View
    {
        $teacher = $request->user();

        $property->load([
            'assignments' => function ($query) use ($teacher) {
                $query
                    ->with(['assignedBy', 'propertyUnits'])
                    ->where('teacher_id', $teacher->id)
                    ->where('status', Assignment::STATUS_ACTIVE)
                    ->latest('date_assigned');
            },
            'activeAssignments',
        ]);

        abort_unless($property->assignments->isNotEmpty(), 403);
        $assignment = $property->assignments->first();

        return view('teacher.properties.show', [
            'property' => $property,
            'assignment' => $assignment,
        ]);
    }
}
