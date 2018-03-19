<?php

namespace Modules\Content\Models;

use Nova\Database\ORM\Builder;


class TaxonomyBuilder extends Builder
{
    /**
     * @return TaxonomyBuilder
     */
    public function category()
    {
        return $this->where('taxonomy', 'category');
    }

    /**
     * @return \Modules\Content\Models\TaxonomyBuilder
     */
    public function menu()
    {
        return $this->where('taxonomy', 'nav_menu');
    }

    /**
     * @param string $name
     * @return \Modules\Content\Models\TaxonomyBuilder
     */
    public function name($name)
    {
        return $this->where('taxonomy', $name);
    }

    /**
     * @param string $slug
     * @return \Modules\Content\Models\TaxonomyBuilder
     */
    public function slug($slug = null)
    {
        if (! is_null($slug) && ! empty($slug)) {
            return $this->whereHas('term', function ($query) use ($slug)
            {
                $query->where('slug', $slug);
            });
        }

        return $this;
    }

    /**
     * @param null $slug
     * @return \Modules\Content\Models\TaxonomyBuilder
     */
    public function term($slug = null)
    {
        return $this->slug($slug);
    }
}
