<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientsRecords extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patients_records';

	/**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

	 /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

	/**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    // protected $keyType = 'string';

	/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

	const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = ['name'];

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

	public function status(): BelongsTo
	{
		return $this->belongsTo(PatientsStatus::class, 'status', 'status');
	}

    public function patientId(): BelongsTo
	{
		return $this->belongsTo(Patients::class, 'patient_id', 'id');
	}

}