<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use VoltSoft\FnsSmz\FiscalSignatureData;

/**
 * App\Models\Payout
 *
 * @property int $id
 * @property int $project_id
 * @property int $task_id
 * @property int $user_id
 * @property int $job_category_id
 * @property float $sum
 * @property string $status
 * @property string|null $description
 * @property string|null $error_description
 * @property string|null $payment_id
 * @property string|null $receipt_id
 * @property string|null $receipt_url
 * @property string|null $receipt_error
 * @property string|null $receipt_uuid
 * @property string|null $receipt_qr
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $created_date
 * @property-read \App\Models\JobCategory|null $jobCategory
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Task $task
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Payout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payout query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereErrorDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereJobCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereReceiptError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereReceiptId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereReceiptQr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereReceiptUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereReceiptUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereTaskId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payout whereUserId($value)
 * @mixin \Eloquent
 */
class Payout extends Model
{
    use HasFactory;

    /**
     * ?????????????? ?? ?????????????????? ??????????????
     */
    protected $table = "payouts";
    protected $primaryKey = "id";
    protected $keyType = "integer";
    public $incrementing = true;
    public $timestamps = true;

    public $fillable = [
        'project_id',
        'task_id',
        'description',
        'user_id',
        'job_category_id',
        'error_description',
        'sum',
        'status',
    ];

    public $hidden = [];

    public $appends = [];

    const STATUS_DRAFT    = 'draft';
    const STATUS_COMPLETE = 'complete';
    const STATUS_PROCESS  = 'process';
    const STATUS_CANCELED = 'canceled';
    const STATUS_ERROR    = 'error';

    /**
     * ?????????????????? ????????????
     */
    protected static function boot()
    {
        parent::boot();
        //self::observe(new \App\Observers\PayoutObserver);
    }

    /**
     * ????????????
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * ??????????????????????
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * ????????????
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    /**
     * ?????? ??????????
     */
    public function jobCategory()
    {
        return $this->hasOne(JobCategory::class, 'id', 'job_category_id');
    }

    /**
     * ???????????????????? ???????????? ???? ???????????????? ?????? ?????????????? ????????
     *
     * @param string $inn ?????? ????????????????????????
     * @param string $receiptId ID ????????
     * @return Payout|null
     */
    public static function getForOfflineReceipt(string $inn, string $receiptId): ?Payout
    {
        return self::where('receipt_id', $receiptId)
            ->whereExists(function ($query) use ($inn) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->where('users.inn', $inn);
            })
            ->first();
    }

    /**
     *
     */
    public function getCreatedDateAttribute()
    {
        return $this->created_at->format('d.m.Y');
    }

    /**
     * ???????????????? ?????????? ???????????????? ??????????????
     *
     * @return string
     */
    public function getCreatedTime(): string
    {
        return $this->created_at->format('H:i');
    }

    /**
     * ???????????????????????? ????????????
     */
    public function getTranslatedStatus(): string
    {
        $status = '';
        switch ($this->status) {
            case self::STATUS_CANCELED:
                $status = '??????????????????????';
                break;
            case self::STATUS_COMPLETE:
                $status = '??????????????';
                break;
            case self::STATUS_DRAFT:
                $status = '????????????????';
                break;
            case self::STATUS_ERROR:
                $status = '????????????';
                break;
            case self::STATUS_PROCESS:
                $status = '?? ????????????????';
                break;
        }
        return $status;
    }
    /**
     * ???????????????? ?????????????????? ???????? ???????? ???????????????? ??????????????
     *
     * @return string
     */
    public function getCreatedTimeZone(): string
    {
        return $this->created_at->getOffsetString();
    }

    /**
     * ???????????? ???????????? ???????????????????? ?????????????? ????????
     *
     * @param FiscalSignatureData $fiscalSignature
     * @return bool
     */
    public function setOfflineReceiptData(FiscalSignatureData $fiscalSignature): bool
    {
        $this->receipt_id   = $fiscalSignature->receiptId;
        $this->receipt_uuid = $fiscalSignature->incomeHashCode;
        $this->receipt_url  = $this->getReceiptUrl();

        return $this->save();
    }

    /**
     * ???????????????? url ????????
     *
     * @param bool $approved
     * @return string
     */
    public function getReceiptUrl(bool $approved = false): string
    {
        //        $stand = $approved ? 'https://himself-ktr.nalog.ru/' : 'https://api2.izanyat.ru/';
        // $stand = $approved ? 'https://lknpd-adp.gnivc.ru/' : config('app.url') . "/";
        $stand = $approved ? config('fns.receipt_url') : config('app.url') . "/";

        return $stand . 'api/v1/receipt/' . $this->user->inn . '/' . $this->receipt_id . '/print';
    }

    /**
     * ???????????? ????????????
     *
     * @param $status
     * @return bool
     */
    public function setStatus($status): bool
    {
        $this->status = $status;
        $this->save();

        return true;
    }

    /**
     * ???????????? ???????????? "????????????????"
     * @return bool
     */
    public function setStatusDraft(): bool
    {
        return $this->setStatus(self::STATUS_DRAFT);
    }

    /**
     * ???????????? ???????????? "????????????"
     * @return bool
     */
    public function setStatusError(): bool
    {
        return $this->setStatus(self::STATUS_ERROR);
    }

    /**
     * ???????????? ???????????? "??????????????????????"
     * @return bool
     */
    public function setStatusCanceled(): bool
    {
        return $this->setStatus(self::STATUS_CANCELED);
    }

    /**
     * ???????????? ???????????? "????????????????"
     * @return bool
     */
    public function setStatusComplete(): bool
    {
        return $this->setStatus(self::STATUS_COMPLETE);
    }

    /**
     * ???????????????????? ???????????? ????????????????
     * @return bool
     */
    public function calculateBalance(): bool
    {
        $this->project->company->balance -= $this->sum;
        $this->project->company->save();

        return true;
    }

    /**
     * ?????????????????? ????????????
     *
     * @param $message
     * @return bool
     */
    public function saveError($message): bool
    {
        $this->description = $message;
        return $this->setStatusError();
    }
}
