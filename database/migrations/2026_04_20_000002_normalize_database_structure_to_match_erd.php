<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('location_name')->unique();
            $table->string('building')->nullable();
            $table->string('room')->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('property_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name')->unique();
            $table->text('description')->nullable();
        });

        Schema::create('disposal_methods', function (Blueprint $table) {
            $table->id();
            $table->string('method_name')->unique();
            $table->text('description')->nullable();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('department')->nullable();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_number')->nullable();
            $table->string('department')->nullable();
            $table->index('employee_number');
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_number')->nullable();
            $table->string('subject_area')->nullable();
            $table->index('employee_number');
        });

        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('reference_type');
            $table->string('approval_status')->default('pending');
            $table->date('approval_date')->nullable();
            $table->text('remarks')->nullable();
        });

        Schema::create('property_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('action_type');
            $table->string('reference')->nullable();
            $table->date('action_date');
            $table->text('remarks')->nullable();
        });

        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->date('return_date');
            $table->string('status')->default('returned');
            $table->text('remarks')->nullable();
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->after('created_by')->constrained('staff')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->after('location')->constrained('locations')->nullOnDelete();
            $table->foreignId('property_category_id')->nullable()->after('category')->constrained('property_categories')->nullOnDelete();
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('teacher_profile_id')->nullable()->after('teacher_id')->constrained('teachers')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->after('assigned_by')->constrained('staff')->nullOnDelete();
            $table->date('expected_return_date')->nullable()->after('date_assigned');
            $table->foreignId('location_id')->nullable()->after('location')->constrained('locations')->nullOnDelete();
        });

        Schema::table('disposals', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('disposed_by')->constrained('admins')->nullOnDelete();
            $table->foreignId('disposal_method_id')->nullable()->after('disposal_method')->constrained('disposal_methods')->nullOnDelete();
        });

        $this->seedPropertyCategories();
        $this->seedLocations();
        $this->seedDisposalMethods();
        $this->seedRoleProfiles();
        $this->backfillProperties();
        $this->backfillAssignments();
        $this->backfillDisposals();
    }

    public function down(): void
    {
        Schema::table('disposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_id');
            $table->dropConstrainedForeignId('disposal_method_id');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_profile_id');
            $table->dropConstrainedForeignId('staff_id');
            $table->dropConstrainedForeignId('location_id');
            $table->dropColumn('expected_return_date');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_id');
            $table->dropConstrainedForeignId('location_id');
            $table->dropConstrainedForeignId('property_category_id');
        });

        Schema::dropIfExists('returns');
        Schema::dropIfExists('property_histories');
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('admins');
        Schema::dropIfExists('disposal_methods');
        Schema::dropIfExists('property_categories');
        Schema::dropIfExists('locations');
    }

    private function seedPropertyCategories(): void
    {
        $this->collectDistinctValues('properties', 'category')
            ->each(fn (string $name) => DB::table('property_categories')->insertOrIgnore([
                'category_name' => $name,
                'description' => null,
            ]));
    }

    private function seedLocations(): void
    {
        $propertyLocations = $this->collectDistinctValues('properties', 'location');
        $assignmentLocations = $this->collectDistinctValues('assignments', 'location');

        $propertyLocations
            ->merge($assignmentLocations)
            ->unique()
            ->values()
            ->each(fn (string $name) => DB::table('locations')->insertOrIgnore([
                'location_name' => $name,
                'building' => null,
                'room' => null,
                'description' => null,
            ]));
    }

    private function seedDisposalMethods(): void
    {
        collect([
            'auction',
            'donation',
            'recycling',
            'transfer',
            'condemnation',
            'destruction',
        ])
            ->merge($this->collectDistinctValues('disposals', 'disposal_method'))
            ->unique()
            ->values()
            ->each(fn (string $name) => DB::table('disposal_methods')->insertOrIgnore([
                'method_name' => $name,
                'description' => null,
            ]));
    }

    private function seedRoleProfiles(): void
    {
        DB::table('users')
            ->select(['id', 'role'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user): void {
                if ($user->role === 'admin') {
                    DB::table('admins')->insertOrIgnore([
                        'user_id' => $user->id,
                        'department' => null,
                    ]);
                }

                if ($user->role === 'staff') {
                    DB::table('staff')->insertOrIgnore([
                        'user_id' => $user->id,
                        'employee_number' => null,
                        'department' => null,
                    ]);
                }

                if ($user->role === 'teacher') {
                    DB::table('teachers')->insertOrIgnore([
                        'user_id' => $user->id,
                        'employee_number' => null,
                        'subject_area' => null,
                    ]);
                }
            });
    }

    private function backfillProperties(): void
    {
        $categoryMap = DB::table('property_categories')->pluck('id', 'category_name');
        $locationMap = DB::table('locations')->pluck('id', 'location_name');
        $staffMap = DB::table('staff')->pluck('id', 'user_id');

        DB::table('properties')
            ->select(['id', 'category', 'location', 'created_by'])
            ->orderBy('id')
            ->get()
            ->each(function (object $property) use ($categoryMap, $locationMap, $staffMap): void {
                DB::table('properties')
                    ->where('id', $property->id)
                    ->update([
                        'property_category_id' => $this->mapLookupValue($categoryMap, $property->category),
                        'location_id' => $this->mapLookupValue($locationMap, $property->location),
                        'staff_id' => $staffMap[$property->created_by] ?? null,
                    ]);
            });
    }

    private function backfillAssignments(): void
    {
        $teacherMap = DB::table('teachers')->pluck('id', 'user_id');
        $staffMap = DB::table('staff')->pluck('id', 'user_id');
        $locationMap = DB::table('locations')->pluck('id', 'location_name');

        DB::table('assignments')
            ->select(['id', 'teacher_id', 'assigned_by', 'location'])
            ->orderBy('id')
            ->get()
            ->each(function (object $assignment) use ($teacherMap, $staffMap, $locationMap): void {
                DB::table('assignments')
                    ->where('id', $assignment->id)
                    ->update([
                        'teacher_profile_id' => $teacherMap[$assignment->teacher_id] ?? null,
                        'staff_id' => $staffMap[$assignment->assigned_by] ?? null,
                        'location_id' => $this->mapLookupValue($locationMap, $assignment->location),
                    ]);
            });
    }

    private function backfillDisposals(): void
    {
        $adminMap = DB::table('admins')->pluck('id', 'user_id');
        $methodMap = DB::table('disposal_methods')->pluck('id', 'method_name');

        DB::table('disposals')
            ->select(['id', 'disposed_by', 'disposal_method'])
            ->orderBy('id')
            ->get()
            ->each(function (object $disposal) use ($adminMap, $methodMap): void {
                DB::table('disposals')
                    ->where('id', $disposal->id)
                    ->update([
                        'admin_id' => $adminMap[$disposal->disposed_by] ?? null,
                        'disposal_method_id' => $this->mapLookupValue($methodMap, $disposal->disposal_method),
                    ]);
            });
    }

    private function collectDistinctValues(string $table, string $column): Collection
    {
        return DB::table($table)
            ->whereNotNull($column)
            ->pluck($column)
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter(fn (string $value) => $value !== '')
            ->unique()
            ->values();
    }

    private function mapLookupValue(Collection $map, mixed $value): ?int
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return $map[$normalized] ?? null;
    }
};
