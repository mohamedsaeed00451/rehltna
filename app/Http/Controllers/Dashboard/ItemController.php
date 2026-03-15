<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\ItemsErrorUploadedExport;
use App\Exports\ItemsTempExport;
use App\Http\Controllers\Controller;
use App\Jobs\ImportItemsJob;
use App\Jobs\SendPushNotificationJob;
use App\Models\City;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\NotificationTemplate;
use App\Models\ResidencyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): view
    {
        $items = Item::query()->orderByDesc('id')->paginate(10);
        $templates = NotificationTemplate::all();
        return view('pages.items.index', compact('items', 'templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): view
    {
        $itemTypes = ItemType::all();
        $cities = City::all();
        return view('pages.items.create', compact('itemTypes', 'cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        try {

            $activeLangs = get_active_langs();

            $fieldsToExclude = ['meta_img', 'pdf', 'gallery', 'itinerary_city_id', 'itinerary_start', 'itinerary_end', 'itinerary_nights'];
            foreach ($activeLangs as $lang) {
                $fieldsToExclude[] = 'banner_' . $lang;
            }

            $data = $request->except($fieldsToExclude);

            if (empty($data['discount'])) {
                $data['discount'] = 0;
            }

            foreach ($activeLangs as $lang) {
                if ($request->filled('banner_' . $lang)) {
                    $data['banner_' . $lang] = $this->cleanPath($request->input('banner_' . $lang));
                }
            }

            if ($request->filled('meta_img')) {
                $data['meta_img'] = $this->cleanPath($request->input('meta_img'));
            }

            if ($request->hasFile('pdf')) {
                $data['pdf'] = uploadFile($request->file('pdf'), 'pdfs', 'pdf');
            }

            $item = Item::query()->create($data);
            $item->setAttribute('order', $request->get('order'));
            $item->save();

            if ($request->has('gallery')) {
                $item->galleries()->delete();
                foreach ($request->input('gallery') as $imagePath) {
                    $item->galleries()->create(['image' => $this->cleanPath($imagePath)]);
                }
            }

            if ($request->has('itinerary_city_id')) {
                $cityIds = $request->input('itinerary_city_id');
                $starts = $request->input('itinerary_start');
                $ends = $request->input('itinerary_end');
                $nights = $request->input('itinerary_nights');

                foreach ($cityIds as $index => $cityId) {
                    if (!empty($cityId) && !empty($starts[$index])) {
                        $item->itineraries()->create([
                            'city_id' => $cityId,
                            'start_date' => $starts[$index],
                            'end_date' => $ends[$index],
                            'nights' => $nights[$index] ?? 0,
                        ]);
                    }
                }
            }

            try {
                $template = NotificationTemplate::inRandomOrder()->first();
                if ($template) {

                    $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $template->title . $template->body);

                    if ($isArabic) {
                        $tripName = $item->title_ar ?? $item->title_en ?? 'Our Trip';
                    } else {
                        $tripName = $item->title_en ?? $item->title_ar ?? 'Our Trip';
                    }

                    $title = str_replace('{trip_name}', $tripName, $template->title);
                    $body = str_replace('{trip_name}', $tripName, $template->body);

                    $tokens = ResidencyUser::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

                    foreach ($tokens as $token) {
                        SendPushNotificationJob::dispatch(
                            $token,
                            $title,
                            $body,
                            ['type' => 'new_trip', 'trip_id' => (string)$item->id]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error sending push notification for new trip: " . $e->getMessage());
            }

            return redirect()->route('items.index')->with('success', 'Item created successfully.');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Oops! Something went wrong');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): view
    {
        $item = Item::query()->findOrFail(decrypt($id));
        $itemTypes = ItemType::all();
        $cities = City::all();
        return view('pages.items.edit', compact('item', 'itemTypes', 'cities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        try {

            $item = Item::query()->findOrFail($id);

            $activeLangs = get_active_langs();

            $fieldsToExclude = ['meta_img', 'pdf', 'gallery', 'itinerary_city_id', 'itinerary_start', 'itinerary_end', 'itinerary_nights'];
            foreach ($activeLangs as $lang) {
                $fieldsToExclude[] = 'banner_' . $lang;
            }

            $data = $request->except($fieldsToExclude);

            if (empty($data['discount'])) {
                $data['discount'] = 0;
            }

            foreach ($activeLangs as $lang) {
                if ($request->filled('banner_' . $lang)) {
                    $data['banner_' . $lang] = $this->cleanPath($request->input('banner_' . $lang));
                }
            }

            if ($request->filled('meta_img')) {
                $data['meta_img'] = $this->cleanPath($request->input('meta_img'));
            }

            if ($request->hasFile('pdf')) {
                if ($item->pdf) deleteFiles([$item->pdf]);
                $data['pdf'] = uploadFile($request->file('pdf'), 'pdfs', 'pdf');
            }

            if ($request->has('gallery')) {
                $item->galleries()->delete();
                foreach ($request->input('gallery') as $imagePath) {
                    $item->galleries()->create(['image' => $this->cleanPath($imagePath)]);
                }
            } elseif ($request->has('gallery_cleared')) {
                $item->galleries()->delete();
            }

            if ($request->has('itinerary_city_id')) {
                $item->itineraries()->delete();
                $cityIds = $request->input('itinerary_city_id');
                $starts = $request->input('itinerary_start');
                $ends = $request->input('itinerary_end');
                $nights = $request->input('itinerary_nights');

                foreach ($cityIds as $index => $cityId) {
                    if (!empty($cityId) && !empty($starts[$index])) {
                        $item->itineraries()->create([
                            'city_id' => $cityId,
                            'start_date' => $starts[$index],
                            'end_date' => $ends[$index],
                            'nights' => $nights[$index] ?? 0,
                        ]);
                    }
                }
            } else {
                $item->itineraries()->delete();
            }

            $item->fill($data);
            $item->setAttribute('order', $request->get('order'));
            $item->save();

            return redirect()->route('items.index')->with('success', 'Item updated successfully.');

        } catch (\Exception $e) {
            Log::error("Error update item: " . $e->getMessage());
            return redirect()->back()->with('error', 'Oops! Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        try {

            $item = Item::query()->findOrFail($id);
            $item->galleries()->delete();
            $item->delete();

            return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Oops! Something went wrong');
        }
    }

    private function cleanPath($path): array|string
    {
        return str_replace(url('/'), '', $path);
    }

    public function itemsChangeStatus($id): JsonResponse
    {
        $item = Item::query()->findOrFail($id);
        if ($item->status == 1) {
            $item->status = 0;
            $item->save();
        } else {
            $item->status = 1;
            $item->save();
        }
        return response()->json(['status' => $item->status]);
    }

    public function itemsChangeIsFeature($id): JsonResponse
    {
        $item = Item::query()->findOrFail($id);

        if ($item->is_feature == 1) {
            $item->is_feature = 0;
            $item->featured_at = null;
        } else {
            $item->is_feature = 1;
            $item->featured_at = now();

            try {

                $tripName = $item->title_en ?? $item->title_ar ?? 'Our Trip';
                $title = "🌟 Special Offer Alert!";
                $body = "Don't miss out! {$tripName} is now on a special offer for the next 7 days.";

                $tokens = ResidencyUser::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

                foreach ($tokens as $token) {
                    SendPushNotificationJob::dispatch(
                        $token,
                        $title,
                        $body,
                        ['type' => 'special_offer', 'trip_id' => (string)$item->id]
                    );
                }

            } catch (\Exception $e) {
                Log::error("Error sending push notification for special offer: " . $e->getMessage());
            }
        }

        $item->save();

        return response()->json(['is_feature' => $item->is_feature]);
    }

    public function changeOutOfStock($id): JsonResponse
    {
        $item = Item::query()->findOrFail($id);
        $item->out_of_stock = $item->out_of_stock == 1 ? 0 : 1;
        $item->save();

        return response()->json(['out_of_stock' => $item->out_of_stock]);
    }

    public function sendCustomNotification(Request $request, $id): JsonResponse
    {
        try {
            $item = Item::query()->findOrFail($id);
            $template = NotificationTemplate::findOrFail($request->template_id);

            $isArabic = preg_match('/[\x{0600}-\x{06FF}]/u', $template->title . $template->body);

            if ($isArabic) {
                $tripName = $item->title_ar ?? $item->title_en ?? 'Our Trip';
            } else {
                $tripName = $item->title_en ?? $item->title_ar ?? 'Our Trip';
            }

            $title = str_replace('{trip_name}', $tripName, $template->title);
            $body = str_replace('{trip_name}', $tripName, $template->body);

            $tokens = ResidencyUser::whereNotNull('fcm_token')->pluck('fcm_token')->toArray();

            foreach ($tokens as $token) {
                SendPushNotificationJob::dispatch(
                    $token,
                    $title,
                    $body,
                    ['type' => 'custom_alert', 'trip_id' => (string)$item->id]
                );
            }

            return response()->json(['message' => 'Notifications are being sent in the background!']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    public function exportItemsTempExcel(): BinaryFileResponse
    {
        return Excel::download(new ItemsTempExport, 'itemsTemp.xlsx');
    }

    public function showUploadForm()
    {
        return view('pages.items.upload');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls,txt'
        ]);

        $path = $request->file('file')->getRealPath();
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $header = array_map('trim', $rows[1]);

        Log::info('Import started', ['file' => $request->file('file')->getClientOriginalName()]);

        $activeLangs = get_active_langs();

        foreach (array_slice($rows, 1) as $index => $row) {
            if (empty($row['A']) && empty($row['B'])) {
                Log::warning("Row {$index} skipped - empty row", $row);
                continue;
            }

            try {

                $data = [
                    'price' => $row[array_search('Price', $header)] ?? 0,
                    'status' => $row[array_search('Status', $header)] ?? 1,
                    'is_feature' => $row[array_search('Is Feature', $header)] ?? 0,
                    'item_type_id' => $row[array_search('Item Type', $header)] ?? null,
                ];

                foreach ($activeLangs as $lang) {

                    $langSuffix = ucfirst($lang);

                    $data["title_{$lang}"] = $row[array_search("Title {$langSuffix}", $header)] ?? null;
                    $data["short_description_{$lang}"] = $row[array_search("Short Description {$langSuffix}", $header)] ?? null;
                    $data["description_{$lang}"] = $row[array_search("Description {$langSuffix}", $header)] ?? null;
                    $data["meta_title_{$lang}"] = $row[array_search("Meta Title {$langSuffix}", $header)] ?? null;
                    $data["meta_description_{$lang}"] = $row[array_search("Meta Description {$langSuffix}", $header)] ?? null;
                    $data["meta_keywords_{$lang}"] = $row[array_search("Meta Keywords {$langSuffix}", $header)] ?? null;

                    if (!empty($data["title_{$lang}"])) {
                        $data["slug_{$lang}"] = $this->generateSlug("slug_{$lang}", $data["title_{$lang}"]);
                    }
                }

                $imageLink = $row[array_search('Image 1', $header)] ?? null;
                if ($imageLink) {

                    $data['meta_img'] = $this->downloadGoogleImage($imageLink, 'meta', 'meta');

                    foreach ($activeLangs as $lang) {
                        $data["banner_{$lang}"] = $this->downloadGoogleImage($imageLink, 'banners', $lang);
                    }
                }

                $item = Item::create($data);

                $imageLink2 = $row[array_search('Image 2', $header)] ?? null;
                if ($imageLink2) {
                    $path = $this->downloadGoogleImage($imageLink2, 'gallery', 'gallery');
                    if ($path) {
                        $item->galleries()->create(['image' => $path]);
                    }
                }

                Log::info("Row {$index} imported successfully"); // Removed specific title log to avoid errors if title_en is empty

            } catch (\Exception $e) {
                Log::error("Row {$index} failed to import", [
                    'error' => $e->getMessage(),
                    'row' => $row
                ]);
            }
        }

        Log::info('Import finished');

        return back()->with('success', 'Items imported successfully!');
    }

    private function downloadGoogleImage($url, string $dist, string $prefix = null)
    {
        try {
            preg_match('/\/d\/(.*?)\//', $url, $matches);
            if (!isset($matches[1])) {
                return null;
            }

            $fileId = $matches[1];
            $downloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}";

            $response = Http::get($downloadUrl);
            if ($response->successful()) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $response->body());
                finfo_close($finfo);

                $extension = match ($mime) {
                    'image/jpeg' => 'jpeg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    'image/jpg' => 'jpg',
                    default => 'jpg',
                };

                $filename = uniqid() . '_' . time() . ($prefix ? "_{$prefix}" : '') . '.' . $extension;
                $path = "uploads/tenant_" . getTenantId() . "/{$dist}/" . $filename;

                if (!file_exists(public_path("uploads/tenant_" . getTenantId() . "/{$dist}"))) {
                    mkdir(public_path("uploads/tenant_" . getTenantId() . "/{$dist}"), 0777, true);
                }

                file_put_contents(public_path($path), $response->body());

                return $path;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }


    public function generateSlug($slugModel, $title)
    {
        $slug = Str::slug($title, '-');
        $original = $slug;
        $counter = 1;

        while (Item::where($slugModel, $slug)->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function exportItemsErrorUploadedExcel(): BinaryFileResponse
    {
        return Excel::download(new ItemsErrorUploadedExport, 'itemsErrorUploaded.xlsx');
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);
        $file = $request->file('excel_file');
        $fileName = uniqid() . '_' . time() . '_' . $file->getClientOriginalName();
        $filePath = public_path('uploads/tenant_' . getTenantId() . '/excels');
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $file->move($filePath, $fileName);
        ImportItemsJob::dispatch("uploads/tenant_" . getTenantId() . "/excels/{$fileName}");

        return back()->with('success', 'File uploaded successfully, import will start in background.');
    }

}
