<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\RecordReadingAction;
use App\Http\Requests\StoreReadingRequest;
use Illuminate\Http\Response;

final class EnviroController extends Controller
{
    public function invoke(StoreReadingRequest $request, RecordReadingAction $recordReadingAction): Response
    {
        $saved_reading = $recordReadingAction->execute($request->validated());

        return $saved_reading ? response(status: 200) : response(status: 400);
    }
}
