<?php

namespace Modules\Content\Controllers;

use Nova\Http\Request;
use Nova\Routing\Controller as BaseController;
use Nova\Support\Facades\App;
use Nova\Support\Facades\Config;
use Nova\Support\Facades\File;
use Nova\Support\Facades\Response;
use Nova\Support\Str;

use Modules\Content\Models\Attachment;

use Intervention\Image\ImageManagerStatic as Image;


class Attachments extends BaseController
{

    public function serve(Request $request, $name)
    {
        $upload = Attachment::where("name", $name)->firstOrFail();

        //
        $basePath = Config::get('content::attachments.path', base_path('assets/files'));

        $path = $basePath .DS .$name;

        if (! File::exists($path)) {
            abort(404);
        }

        // Check if Thumbnail
        $size = $request->input('s');

        if (isset($size) && Str::is('image/*', $upload->mime_type)) {
            $basePath = Config::get('content::attachments.thumbPath', base_path('assets/files/thumbnails'));

            if (! is_numeric($size)) {
                $size = 150;
            }

            $basePath .= DS .$size;

            if (! File::exists($basePath)) {
                File::makeDirectory($basePath, 0755, true, true);
            }

            $filePath = $basePath .DS .$name;

            if (! File::exists($filePath)) {
                $image = Image::make($path);

                $image->fit($size, $size, function ($constraint)
                {
                    $constraint->aspectRatio();
                });

                $image->save($filePath);
            }

            $path = $filePath;
        }

        $download = $request->input('download');

        $disposition = isset($download) ? 'attachment' : 'inline';

        // Create a Assets Dispatcher instance.
        $dispatcher = App::make('assets.dispatcher');

        return $dispatcher->serve($path, $request, $disposition, $upload->title);
    }
}
