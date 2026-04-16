<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActionItem extends Model
{
    protected $fillable = [
        'mining_department_id', 'comment', 'priority',
        'status', 'due_date', 'reported_date',
    ];

    protected $casts = [
        'due_date'      => 'date',
        'reported_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(MiningDepartment::class, 'mining_department_id');
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'completed'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function dueLabel(): string
    {
        if ($this->due_date === null) return '—';
        if ($this->isOverdue())      return 'Over Due';
        return $this->due_date->format('d M Y');
    }

    public static function overdueCount(): int
    {
        return self::whereNotIn('status', ['completed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->count();
    }

    public static function priorityLabel(string $p): string
    {
        return match($p) { 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low', default => $p };
    }

    public static function statusLabel(string $s): string
    {
        return match($s) {
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'pending'     => 'Pending',
            'completed'   => 'Completed',
            default       => $s,
        };
    }
}
