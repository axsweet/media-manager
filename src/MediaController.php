<?php

namespace Encore\Admin\Media;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        return Admin::content(function (Content $content) use ($request) {
            $content->header('Media manager');

            $path = $request->get('path', '/');
            $view = $request->get('view', 'table');

            $manager = new MediaManager($path);
            $list = $manager->ls($request->query());

            $content->body(view("laravel-admin-media::$view", [
                'list'   => $list['files'],
                'paginator' => $list['paginator']->links('admin::pagination')->toHtml(),
                'nav'    => $manager->navigation(),
                'url'    => $manager->urls(),
            ]));
        });
    }

    public function download(Request $request)
    {
        $file = $request->get('file');

        $manager = new MediaManager($file);

        try {
            return $manager->download();
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function upload(Request $request)
    {
        $files = $request->file('files');
        $dir = $request->get('dir', '/');

        $manager = new MediaManager($dir);

        try {
            if ($manager->upload($files)) {
                admin_toastr(trans('admin.upload_succeeded'));
            }
        } catch (\Exception $e) {
            admin_toastr($e->getMessage(), 'error');
        }

        return back();
    }

    public function delete(Request $request)
    {
        $files = $request->get('files');

        $manager = new MediaManager();

        try {
            if ($manager->delete($files)) {
                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.delete_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function clearShowCache()
    {

       Artisan::call('command:cdnnew');
        $art = Artisan::output();


        admin_success('Done. Cleared Cache and synced show.',$art);
        return back();
    }
    public function move(Request $request)
    {
        $path = $request->get('path');
        $new = $request->get('new');

        $manager = new MediaManager($path);

        try {
            if ($manager->move($new)) {
                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.move_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function newFolder(Request $request)
    {
        $dir = $request->get('dir');
        $name = $request->get('name');

        $manager = new MediaManager($dir);

        try {
            if ($manager->newFolder($name)) {
                return response()->json([
                    'status'  => true,
                    'message' => trans('admin.move_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}