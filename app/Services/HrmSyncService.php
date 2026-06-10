<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HrmSyncService
{
    protected array $yurDepIds;

    public function __construct()
    {
        $this->yurDepIds = array_map('trim', explode(',', env('YUP_DEP_IDS', '177')));
    }

    public function syncAll(bool $fresh = false): void
    {
        Log::info('HRM sync boshlandi: ' . now());

        DB::purge('hrm');

        $this->syncDepartments($fresh);
        $this->syncEmployeeStaff();
        $this->syncStaff();
        $this->syncPositions($fresh);
        $this->syncEmployees();

        Log::info('HRM sync tugadi: ' . now());
    }

    public function syncDepartments(bool $fresh): void
    {
        if ($fresh) {
            DB::table('department_translations')->truncate();
            DB::table('departments')->truncate();
        }

        DB::connection('hrm')
            ->table('department')
            ->whereIn('yur_dep_id', $this->yurDepIds)
            ->orderBy('id')
            ->chunk(1000, function ($hrmDepartments) {
                $departments = [];
                $translations = [];

                foreach ($hrmDepartments as $dep) {
                    $departments[] = [
                        'id' => $dep->id,
                        'code' => $dep->code,
                        'sequence' => $dep->level,
                        'parent_id' => $dep->parent_id,
                        'department_type_id' => $dep->department_type_id,
                    ];

                    foreach (['en' => $dep->name_en, 'ru' => $dep->name_ru, 'uz' => $dep->name_lt, 'uc' => $dep->name_uz] as $lang => $name) {
                        $translations[] = [
                            'object_id' => $dep->id,
                            'language_code' => $lang,
                            'name' => $name,
                        ];
                    }
                }

                if ($departments) {
                    DB::table('departments')->upsert(
                        $departments,
                        ['id'],
                        ['code', 'sequence', 'parent_id', 'department_type_id']
                    );

                    DB::table('department_translations')
                        ->whereIn('object_id', array_column($departments, 'id'))
                        ->delete();

                    DB::table('department_translations')->insert($translations);
                }

                $deletedIds = $hrmDepartments->where('delete_status', 1)->pluck('id')->toArray();
                if ($deletedIds) {
                    DB::table('departments')->whereIn('id', $deletedIds)->update(['deleted_at' => now()]);
                }
            });
    }

    public function syncEmployeeStaff(): void
    {
        DB::connection('hrm')
            ->table('employee_work')
            ->whereIn('yur_dep_id', $this->yurDepIds)
            ->orderBy('id')
            ->chunk(1000, function ($hrm) {
                $data = [];

                foreach ($hrm as $row) {
                    $data[] = [
                        'id' => $row->id,
                        'staff_id' => $row->staff_list_id,
                        'employee_id' => $row->employee_id,
                        'department_id' => $row->department_id,
                        'position_id' => $row->position_id,
                        'main_staff' => true,
                        'deleted_at' => $row->old == 1 || $row->end_date ? now() : null,
                    ];
                }

                if ($data) {
                    DB::table('employee_staff')->upsert(
                        $data,
                        ['id'],
                        ['staff_id', 'employee_id', 'department_id', 'position_id', 'main_staff', 'deleted_at']
                    );
                }
            });
    }

    public function syncStaff(): void
    {
        DB::table('employee_staff')
            ->select('staff_id')
            ->orderBy('staff_id')
            ->chunk(1000, function ($chunk) {
                $staffIds = $chunk->pluck('staff_id')->toArray();

                DB::connection('hrm')
                    ->table('staff_list')
                    ->whereIn('id', $staffIds)
                    ->orderBy('id')
                    ->chunk(1000, function ($hrm) {
                        $data = [];

                        foreach ($hrm as $staff) {
                            $data[] = [
                                'id' => $staff->id,
                                'department_id' => $staff->department_id,
                                'position_id' => $staff->staff_position_id,
                                'is_department_director' => (bool) $staff->is_department_director,
                            ];
                        }

                        if ($data) {
                            DB::table('staff')->upsert(
                                $data,
                                ['id'],
                                ['department_id', 'position_id', 'is_department_director']
                            );
                        }

                        $deletedIds = $hrm->where('delete', 1)->pluck('id')->toArray();
                        if ($deletedIds) {
                            DB::table('staff')->whereIn('id', $deletedIds)->update(['deleted_at' => now()]);
                        }
                    });
            });
    }

    public function syncPositions(bool $fresh): void
    {
        if ($fresh) {
            DB::table('position_translations')->truncate();
            DB::table('positions')->truncate();
        }

        DB::table('staff')
            ->select('position_id')
            ->orderBy('position_id')
            ->chunk(1000, function ($chunk) {
                $positionIds = $chunk->pluck('position_id')->toArray();

                DB::connection('hrm')
                    ->table('staff_position')
                    ->whereIn('id', $positionIds)
                    ->orderBy('id')
                    ->chunk(1000, function ($hrm) {
                        $data = [];
                        $translations = [];

                        foreach ($hrm as $pos) {
                            $data[] = [
                                'id' => $pos->id,
                                'code' => $pos->code,
                                'position_type_id' => $pos->position_type_id,
                            ];

                            foreach (['en' => $pos->name_en, 'ru' => $pos->name_ru, 'uz' => $pos->name_lt, 'uc' => $pos->name_uz] as $lang => $name) {
                                $translations[] = [
                                    'object_id' => $pos->id,
                                    'language_code' => $lang,
                                    'name' => $name,
                                ];
                            }
                        }

                        if ($data) {
                            DB::table('positions')->upsert(
                                $data,
                                ['id'],
                                ['code', 'position_type_id']
                            );

                            DB::table('position_translations')
                                ->whereIn('object_id', array_column($data, 'id'))
                                ->delete();

                            DB::table('position_translations')->insert($translations);
                        }

                        $deletedIds = $hrm->where('delete', 1)->pluck('id')->toArray();
                        if ($deletedIds) {
                            DB::table('positions')->whereIn('id', $deletedIds)->update(['deleted_at' => now()]);
                        }
                    });
            });
    }

    public function syncEmployees(): void
    {
        DB::table('employee_staff')
            ->select('employee_id')
            ->orderBy('employee_id')
            ->chunk(1000, function ($chunk) {
                $employeeIds = $chunk->pluck('employee_id')->toArray();

                DB::connection('hrm')
                    ->table('employee')
                    ->whereIn('id', $employeeIds)
                    ->orderBy('id')
                    ->chunk(1000, function ($hrm) {
                        $data = [];

                        foreach ($hrm as $emp) {
                            $data[] = [
                                'id' => $emp->id,
                                'first_name' => $emp->first_name,
                                'last_name' => $emp->last_name,
                                'middle_name' => $emp->parent_name,
                                'tabel' => $emp->table_number,
                                'inn' => $emp->inn,
                                'inps' => $emp->inps,
                            ];
                        }

                        if ($data) {
                            DB::table('employees')->upsert(
                                $data,
                                ['id'],
                                ['first_name', 'last_name', 'middle_name', 'tabel', 'inn', 'inps']
                            );
                        }
                    });
            });
    }
}