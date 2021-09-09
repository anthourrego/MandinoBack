<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id' => 1
            ,'nro_documento' => "0000"
            ,'usuario' => 'admin'
            ,'password' => '$2y$15$Ui5AQTHCS4pkuOFyOAq61OlsjnQdx9rWzmXPDHtol9B0lHj34pU/i'
            ,'nombre1' => 'admin'
            ,'apellido1' => 'admin'
            ,'email' => 'admin@admin.com'
            ,'created_at' => date('Y-m-d H:m:s')
            ,'updated_at' => date('Y-m-d H:m:s')
            ,'fk_municipio' => 21195
            ,'foto' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGQAAABkCAYAAABw4pVUAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OEMyRkUxMUUzNDZEMTFFOEIxNjJDOEUyMEY1OEMzOEQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OEMyRkUxMUYzNDZEMTFFOEIxNjJDOEUyMEY1OEMzOEQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo4QzJGRTExQzM0NkQxMUU4QjE2MkM4RTIwRjU4QzM4RCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo4QzJGRTExRDM0NkQxMUU4QjE2MkM4RTIwRjU4QzM4RCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PnrrxDIAABzISURBVHja7F0JfFTV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'
        ]);
    }
}
