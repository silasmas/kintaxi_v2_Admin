<?php

namespace App\Filament\Resources\SmsOperatorResource\Pages;

use App\Filament\Resources\SmsOperatorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsOperator extends EditRecord
{
  protected static string $resource = SmsOperatorResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\DeleteAction::make(),
    ];
  }
}
