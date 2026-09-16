<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequestSubmissionRequest;
use App\Http\Requests\UpdatePropertyRequestStatusRequest;
use App\Models\PropertyRequestRecord;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyRequestController extends Controller
{
    public function teacherIndex(Request $request): View
    {
        $propertyRequests = PropertyRequestRecord::query()
            ->with('processedBy')
            ->where('requested_by', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('teacher.property-requests.index', [
            'propertyRequests' => $propertyRequests,
            'statuses' => PropertyRequestRecord::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('teacher.property-requests.create', [
            'propertyRequest' => new PropertyRequestRecord(),
        ]);
    }

    public function store(StorePropertyRequestSubmissionRequest $request): RedirectResponse
    {
        PropertyRequestRecord::query()->create([
            'requested_by' => $request->user()->id,
            'requested_item_name' => trim((string) $request->input('requested_item_name')),
            'requested_quantity' => $request->integer('requested_quantity'),
            'needed_by' => $request->date('needed_by'),
            'purpose' => trim((string) $request->input('purpose')),
            'additional_notes' => $this->normalizeText($request->input('additional_notes')),
            'status' => PropertyRequestRecord::STATUS_PENDING,
        ]);

        return redirect()
            ->route('teacher.property-requests.index')
            ->with('success', 'Property request submitted successfully.');
    }

    public function index(): View
    {
        $propertyRequests = PropertyRequestRecord::query()
            ->with(['requester', 'processedBy'])
            ->latest()
            ->paginate(10);

        return view('property-requests.index', [
            'propertyRequests' => $propertyRequests,
            'statuses' => PropertyRequestRecord::statuses(),
        ]);
    }

    public function show(PropertyRequestRecord $propertyRequest): View
    {
        $propertyRequest->load(['requester', 'processedBy']);

        return view('property-requests.show', [
            'propertyRequest' => $propertyRequest,
            'statuses' => PropertyRequestRecord::statuses(),
        ]);
    }

    public function updateStatus(
        UpdatePropertyRequestStatusRequest $request,
        PropertyRequestRecord $propertyRequest
    ): RedirectResponse {
        $propertyRequest->update([
            'status' => $request->input('status'),
            'response_notes' => $this->normalizeText($request->input('response_notes')),
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return redirect()
            ->route('property-requests.show', $propertyRequest)
            ->with('success', 'Property request status updated successfully.');
    }

    private function normalizeText(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
