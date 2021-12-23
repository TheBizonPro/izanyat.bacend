<?php

namespace App\Services;

use App\Enums\Notificatons\NotificationEntities;
use App\Enums\Notificatons\NotificationTypes;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;

class NotificationsService
{

    public function createInvitationNotification(Task $task, User $user): Notification
    {
        $notification = new Notification;
        $notification->user_id = $user->id;
        $notification->is_readed = false;
        $notification->from = $task->project->company->name;
        $notification->subject = 'Приглашение стать исполнителем';
        $notification->text = "Здравствуйте, {$user->firstname}!<br>Вас приглашают стать исполнителем задачи <a href='/contractor/task/{$task->id}'><b>«{$task->name}»</b></a>. Если вас не заинтересовала эта задача, вы можете не откликаться на выполнение этой задачи.";
        $notification->plain_text = "Здравствуйте, {$user->firstname}! Вас приглашают стать исполнителем задачи «{$task->name}». Если вас не заинтересовала эта задача, вы можете не откликаться на выполнение этой задачи.";
        $notification->action = [
            'type' => NotificationTypes::ENTITY,
            'entity' => NotificationEntities::TASK,
            'entity_id' => $task->id
        ];
        $notification->save();

        return $notification;
    }
}
