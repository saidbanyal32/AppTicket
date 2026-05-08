<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $foreignKeys = [
        ['notifications', 'user_id', 'notifications_user_id_foreign', 'cascade'],
        ['ticket_assignments', 'assigned_from', 'ticket_assignments_assigned_from_foreign', 'set null'],
        ['ticket_assignments', 'assigned_to', 'ticket_assignments_assigned_to_foreign', 'restrict'],
        ['ticket_attachments', 'uploaded_by', 'ticket_attachments_uploaded_by_foreign', 'restrict'],
        ['ticket_comments', 'user_id', 'ticket_comments_user_id_foreign', 'restrict'],
        ['ticket_logs', 'user_id', 'ticket_logs_user_id_foreign', 'set null'],
        ['ticket_status_histories', 'changed_by', 'ticket_status_histories_changed_by_foreign', 'restrict'],
        ['tickets', 'requester_id', 'tickets_requester_id_foreign', 'restrict'],
        ['tickets', 'assigned_to', 'tickets_assigned_to_foreign', 'set null'],
        ['tickets', 'resolved_by', 'tickets_resolved_by_foreign', 'set null'],
        ['tickets', 'closed_by', 'tickets_closed_by_foreign', 'set null'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('sys_users')) {
            return;
        }

        $fallbackUserId = DB::table('sys_users')->orderBy('id')->value('id');

        foreach ($this->foreignKeys as [$table, $column, $constraint, $onDelete]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $this->dropConstraint($table, $constraint);
            $this->repairInvalidReferences($table, $column, $onDelete, $fallbackUserId);
            $this->addConstraint($table, $column, $constraint, 'sys_users', $onDelete);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $fallbackUserId = DB::table('users')->orderBy('id')->value('id');

        foreach ($this->foreignKeys as [$table, $column, $constraint, $onDelete]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $this->dropConstraint($table, $constraint);
            $this->repairInvalidReferences($table, $column, $onDelete, $fallbackUserId, 'users');
            $this->addConstraint($table, $column, $constraint, 'users', $onDelete);
        }
    }

    private function dropConstraint(string $table, string $constraint): void
    {
        DB::statement(sprintf('alter table %s drop constraint if exists %s', $this->quote($table), $this->quote($constraint)));
    }

    private function addConstraint(string $table, string $column, string $constraint, string $referencesTable, string $onDelete): void
    {
        DB::statement(sprintf(
            'alter table %s add constraint %s foreign key (%s) references %s(id) on delete %s',
            $this->quote($table),
            $this->quote($constraint),
            $this->quote($column),
            $this->quote($referencesTable),
            $onDelete
        ));
    }

    private function repairInvalidReferences(string $table, string $column, string $onDelete, mixed $fallbackUserId, string $referencesTable = 'sys_users'): void
    {
        if ($onDelete === 'set null') {
            DB::table($table)
                ->whereNotNull($column)
                ->whereNotIn($column, DB::table($referencesTable)->select('id'))
                ->update([$column => null]);

            return;
        }

        if ($fallbackUserId !== null) {
            DB::table($table)
                ->whereNotNull($column)
                ->whereNotIn($column, DB::table($referencesTable)->select('id'))
                ->update([$column => $fallbackUserId]);
        }
    }

    private function quote(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
};
