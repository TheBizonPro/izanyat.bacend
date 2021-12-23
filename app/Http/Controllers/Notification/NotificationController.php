<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\Models\Notification;

use Yajra\DataTables\Facades\DataTables;

use App\Jobs\FNS\MarkNotificationAsRead as FNSMarkNotificationAsRead;
use App\Jobs\FNS\MarkAllNotificationAsRead as FNSMarkAllNotificationAsRead;

class NotificationController extends Controller
{
    /**
     * Получение данных уведомлений в виде таблицы DataTables
     */
    public function datatable(Request $request)
    {
        $me = Auth::user();

        $notifications = Notification::where('user_id', '=', $me->id);

        $dataTable = DataTables::eloquent($notifications);

        $dataTable = $dataTable->addColumn('text', function (Notification $notification) {
            return mb_substr(strip_tags($notification->text), 0, 30) . '...';
        });

        $dataTable = $dataTable->addColumn('created_datetime', function (Notification $notification) {
            return  date('d.m.Y', strtotime($notification->created_at)) . " " . date('H:i', strtotime($notification->created_at));
        });

        $dataTable = $dataTable->orderColumn('created_datetime', function ($query) {
            $query->orderBy('created_at', 'desc');
        });
        $dataTable = $dataTable->smart(true);
        return $dataTable->make(true);
    }

    public function notifications(Request $request)
    {
        $me = Auth::user();

        $notifications = Notification::where('user_id', '=', $me->id)->get();

        return response()->json([
            'title' => 'Успешно',
            'message' => 'Уведомления загружены',
            'notifications' => $notifications
        ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }


    /**
     * Получение количества непрочитанных уведомлений
     */
    public function unreadCount(Request $request)
    {
        $me = Auth::user();
        $notifications_count = Notification::where('user_id', '=', $me->id)
            ->where('is_readed', '=', false)
            ->count();

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Количество уведомлений загружено',
                'notifications_count' => $notifications_count
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }



    /**
     * Получение конкретного уведомления
     */
    public function get(Request $request)
    {
        $me = Auth::user();
        $notification = Notification::where('id', '=', $request->notification_id)
            ->where('user_id', '=', $me->id)
            ->first();

        if ($notification == null) {
            return response()
                ->json([
                    'title' => 'Ошибка',
                    'message' => 'Уведомление не найдено'
                ], 400, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
        }

        if ($notification->is_readed == false) {
            $notification->is_readed = true;
            $notification->save();

            if ($notification->fns_id != null) {
                try {
                    FNSMarkNotificationAsRead::dispatch($notification->user->inn, $notification->fns_id);
                } catch (\Throwable $e) {
                    //2do Logging
                }
            }
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Уведомление загружено',
                'notification' => $notification->toArray(),
                'inn' => $notification->user->inn,
                'fns_id' => $notification->fns_id,
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }



    /**
     * Поменитить все уведомления как прочитанные
     */
    public function readAll(Request $request)
    {
        $me = Auth::user();

        Notification::where('user_id', '=', $me->id)->update(['is_readed' => true]);

        try {
            FNSMarkAllNotificationAsRead::dispatch($me->inn);
        } catch (\Throwable $e) {
            //2do Logging
        }

        return response()
            ->json([
                'title' => 'Успешно',
                'message' => 'Все уведомления помечены как прочитанные!',
            ], 200, [], JSON_UNESCAPED_UNICODE || JSON_UNESCAPED_SLASHES);
    }
}
