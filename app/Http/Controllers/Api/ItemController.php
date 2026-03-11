<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Item;
use App\Models\ItemType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    use ResponseTrait;

    public function getItems(Request $request): JsonResponse
    {
        $query = Item::query()->where('status', 1)->with('galleries', 'itemType', 'itineraries.city');
        if ($request->get('search')) {
            $query = $query->where(function ($query) use ($request) {
                $query->where('title_en', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('title_ar', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('title_fr', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('title_de', 'like', '%' . $request->get('search') . '%');
            });
        }

        $Items = $query->orderByDesc('id')->paginate(10);
        $total = $Items->total();
        $data = [
            'items_count' => $total,
            'items' => $Items,
        ];

        return $this->responseMessage(200, 'success', $data);
    }

    public function getItem($slug): JsonResponse
    {
        $Item = Item::query()->with('galleries', 'itemType', 'itineraries.city')->where(function ($query) use ($slug) {
            $query->where('slug_en', $slug)
                ->orWhere('slug_ar', $slug)
                ->orWhere('slug_fr', $slug)
                ->orWhere('slug_de', $slug);
        })->first();
        if (!$Item)
            return $this->responseMessage(404, 'not found');

        return $this->responseMessage(200, 'success', $Item);
    }

    public function getItemsByItemType($id): JsonResponse
    {
        $itemType = ItemType::query()->find($id);
        if (!$itemType)
            return $this->responseMessage(404, 'not found');

        $items = $itemType->items()->with('galleries', 'itemType', 'itineraries.city')->orderByDesc('id')->paginate(10);
        $data = [
            'itemType' => $itemType,
            'items_count' => $items->total(),
            'items' => $items,
        ];

        return $this->responseMessage(200, 'success', $data);
    }

    public function getItemsFeatures(Request $request): JsonResponse
    {
        $number = $request->get('number') ?? 3;
        $itemsFeatures = Item::query()->with('galleries', 'itemType', 'itineraries.city')->orderByDesc('id')->where('status', 1)->where('is_feature', 1)->take($number)->get();
        return $this->responseMessage(200, 'success', $itemsFeatures);
    }

}
