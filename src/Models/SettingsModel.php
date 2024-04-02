<?php

namespace MCris112\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\System\Settings
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string|null $parent_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, File> $attachments
 * @property-read int|null $attachments_count
 * @method static \Database\Factories\System\SettingsFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel query()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel whereParentKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel whereValue($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SettingsModel> $children
 * @property-read int|null $children_count
 * @property-read SettingsModel|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel children()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel root()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingsModel withChildren()
 * @mixin \Eloquent
 */
class SettingsModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'parent_key'
    ];

    protected $table = "mc_settings";
    // public function attachments()
    // {
    //     return $this->morphToMany(CriFile::class, 'fileable','files_related_morphs', 'fileable_id', 'file_id')->withPivot('field');
    // }

    public function children()
    {
        return $this->hasMany(SettingsModel::class, 'parent_key', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(SettingsModel::class, 'parent_key', 'id');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_key');
    }

    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_key');
    }

    public function scopeWithChildren($query)
    {
        return $query->with(['children' => function ($query) {
            $query->withChildren();
        }]);
    }

    public function findSettingByName(string $sequencial): SettingsModel
    {
        $setting = $this->iterateFindByName($this, explode('.', $sequencial));

        if(!$setting) throw new \Exception('Wrong setting');

        return $setting;
    }

    private function iterateFindByName(SettingsModel $parent, $sequencial, int $position = 0): self | null
    {
        if( !$this->relationLoaded('children') ) return null;

        foreach( $parent->children as $child) {

            if( $child->key == $sequencial[$position])
            {
                if( ($position + 1) < count($sequencial))
                {
                    return $this->iterateFindByName($child, $sequencial, $position + 1);
                }

                return $child;
            }
        }
        return null;
    }
}
