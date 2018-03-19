<?php

namespace Modules\Content\Models;

use Nova\Database\ORM\Model;

use Shared\MetaField\HasMetaFieldsTrait;

use Modules\Content\Models\TaxonomyBuilder;
use Modules\Content\Models\TermMeta;


class Taxonomy extends Model
{
    use HasMetaFieldsTrait;

    //
    protected $table = 'term_taxonomy';

    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = array('term_id', 'taxonomy', 'description', 'parent_id');

    /**
     * @var array
     */
    protected $with = array('term', 'meta');

    /**
     * @var bool
     */
    public $timestamps = false;


    /**
     * @return \Nova\Database\ORM\Relations\HasMany
     */
    public function meta()
    {
        return $this->hasMany('Modules\Content\Models\TermMeta', 'term_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\BelongsTo
     */
    public function term()
    {
        return $this->belongsTo('Modules\Content\Models\Term', 'term_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('Modules\Content\Models\Taxonomy', 'parent_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\BelongsTo
     */
    public function children()
    {
        return $this->hasMany('Modules\Content\Models\Taxonomy', 'parent_id');
    }

    /**
     * @return \Nova\Database\ORM\Relations\BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany(
            'Modules\Content\Models\Post', 'term_relationships', 'term_taxonomy_id', 'object_id'
        );
    }

    /**
     * @return TaxonomyBuilder
     */
    public function newQuery()
    {
        $query = parent::newQuery();

        if (isset($this->taxonomy) && ! empty($this->taxonomy)) {
            return $query->where('taxonomy', $this->taxonomy);
        }

        return $query;
    }

    /**
     * @param \Nova\Database\Query\Builder $query
     * @return TaxonomyBuilder
     */
    public function newQueryBuilder($query)
    {
        return new TaxonomyBuilder($query);
    }

    /**
     * Update the count field.
     */
    public function updateCount()
    {
        $this->count = $this->posts()->count();

        $this->save();
    }

    /**
     * Magic method to return the meta data like the post original fields.
     *
     * @param string $key
     * @return string
     */
    public function __get($key)
    {
        if (! isset($this->$key) && isset($this->term->$key)) {
            return $this->term->$key;
        }

        return parent::__get($key);
    }
}
