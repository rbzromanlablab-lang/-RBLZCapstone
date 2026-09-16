<?php

namespace App\Http\Controllers;

use App\Models\ReturnRecord;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $assigneeId = $request->integer('assignee_id') ?: null;
        $returnedById = $request->integer('returned_by') ?: null;
        $status = trim($request->string('status')->toString());
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        $returns = ReturnRecord::query()
            ->with([
                'assignment.property',
                'assignment.assignee',
                'assignment.propertyUnits',
                'returnedBy',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->whereHas('assignment.property', function ($propertyQuery) use ($search): void {
                            $propertyQuery
                                ->where('property_name', 'like', "%{$search}%")
                                ->orWhere('property_code', 'like', "%{$search}%")
                                ->orWhere('serial_number', 'like', "%{$search}%")
                                ->orWhere('office', 'like', "%{$search}%")
                                ->orWhere('location', 'like', "%{$search}%");
                        })
                        ->orWhereHas('assignment.propertyUnits', function ($unitQuery) use ($search): void {
                            $unitQuery->where('serial_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('assignment.assignee', function ($assigneeQuery) use ($search): void {
                            $assigneeQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('returnedBy', function ($returnedByQuery) use ($search): void {
                            $returnedByQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($assigneeId, fn ($query) => $query->whereHas('assignment', fn ($assignmentQuery) => $assignmentQuery->where('teacher_id', $assigneeId)))
            ->when($returnedById, fn ($query) => $query->where('returned_by', $returnedById))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($dateFrom, fn ($query) => $query->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('return_date', '<=', $dateTo))
            ->latest('return_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('returns.index', [
            'returns' => $returns,
            'assignees' => User::query()
                ->whereIn('role', [User::ROLE_TEACHER, User::ROLE_STAFF])
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
            'returnRecorders' => User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
                ->orderBy('name')
                ->get(['id', 'name', 'role']),
            'statuses' => ReturnRecord::query()
                ->select('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status')
                ->filter()
                ->values(),
        ]);
    }
}
