<?php

use Carbon\Carbon;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Process\Process;
use App\Jobs\SendNotificationJob;
use App\Jobs\AuditLogJob;

function generatePdfRaw($view, $data = [], $options = [])
{
    $html = view($view, $data)->render();

    return Browsershot::html($html)
        ->format($options['format'] ?? 'A4')
        ->margins(10, 10, 10, 10)
        ->showBackground()
        ->timeout(120)
        ->pdf(); // 🔥 returns RAW PDF
}

if (!function_exists('downloadPdf')) {

    function downloadPdf($view, $data = [], $fileName = 'document.pdf', $options = [])
    {
        $html = view($view, $data)->render();

        $pdf = Browsershot::html($html)
            ->format($options['format'] ?? 'A4')
            ->margins(10, 10, 10, 10)
            ->showBackground()
            ->timeout(120);

        return response()->streamDownload(fn() => print($pdf->pdf()), $fileName);
    }
}

function formatDate($date, $format = 'd M Y')
{
    if (!$date) return null;

    return Carbon::parse($date)->format($format);
}

function formatDateTime($date, $format = 'd M Y, h:i A', $default = '-')
{
    if (!$date) return $default;

    try {
        return \Carbon\Carbon::parse($date)->format($format);
    } catch (\Exception $e) {
        return $default;
    }
}

function money($amount, $currency = '₹')
{
    return $currency . number_format($amount, 2);
}

function yesNo($value)
{
    return $value ? 'Yes' : 'No';
}

function shortText($text, $limit = 50)
{
    return \Illuminate\Support\Str::limit($text, $limit);
}

function perPage()
{
    return config('app.pagination_size');
}

function auditLog(
    $module,
    $action,
    $recordId = null,
    $field = null,
    $old = null,
    $new = null,
    $description = null,
    $event = null,
    $affectedUserId = null
) {
    try {

        $data = [
            'user_id' => Auth::id(),
            'hotel_id' => Auth::user()->hotel_id ?? null,

            'module' => $module,
            'action' => $action,
            'event' => $event,
            'description' => $description,

            'url' => Request::fullUrl(),
            'method' => Request::method(),

            'affected_user_id' => $affectedUserId,

            'record_id' => $recordId,
            'field_name' => $field,

            'old_value' => is_array($old) ? json_encode($old) : $old,
            'new_value' => is_array($new) ? json_encode($new) : $new,

            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ];

        // dispatch(function () use ($data) {
        //     \App\Models\AuditLog::create($data);
        // })->afterResponse(); // 🔥 NON-BLOCKING

        AuditLogJob::dispatch($data)->afterResponse();
    } catch (\Exception $e) {
        Log::error('Audit Log Failed: ' . $e->getMessage());
    }
}

if (!function_exists('generateUniqueCode')) {
    function generateUniqueCode($prefix = 'HOT', $length = 6)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // removed confusing chars
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $prefix . '-' . $code;
    }
}

function formatTime($time)
{
    if (!$time) return null;

    return \Carbon\Carbon::parse($time)->format('h:i A');
}

function sendNotification(
    $title,
    $message,
    $type = 'system',
    $receiverId = null,
    $receiverRole = null,
    $referenceType = null,
    $referenceId = null,
    $priority = 'low'
) {
    try {

        $data = [
            'hotel_id' => Auth::user()->hotel_id ?? null,

            'title' => $title,
            'message' => $message,

            'type' => $type,

            'sender_id' => Auth::id(),

            'receiver_id' => $receiverId,
            'receiver_role' => $receiverRole,

            'priority' => $priority,

            'reference_type' => $referenceType,
            'reference_id' => $referenceId,

            'is_read' => 0,
            'is_deleted' => 0,
        ];

        // dispatch(function () use ($data) {
        //     \App\Models\Notification::create($data);
        // })->afterResponse(); // 🔥 NON-BLOCKING

        SendNotificationJob::dispatch($data)->afterResponse();
    } catch (\Exception $e) {
        Log::error('Notification Failed: ' . $e->getMessage());
    }
}

function getHotelAdmin($hotelId)
{
    return User::where('hotel_id', $hotelId)->where('user_role_id', '3')->first();
}

function CodeGenerator(
    string $table,
    string $column,
    string $prefix,
    int $pad = 4,
    $hotelId = null
) {
    return DB::transaction(function () use ($table, $column, $prefix, $pad, $hotelId) {

        $query = DB::table($table)
            ->where($column, 'like', $prefix . '%');

        // 🔥 Hotel-wise sequence
        if ($hotelId) {
            $query->where('hotel_id', $hotelId);
        }

        $lastCode = $query
            ->orderByDesc($column)
            ->lockForUpdate()
            ->value($column);

        if ($lastCode) {
            preg_match('/(\d+)$/', $lastCode, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
    });
}
