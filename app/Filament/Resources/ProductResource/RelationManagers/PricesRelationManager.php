<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\PriceList;
use App\Models\UOM;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';
    
    protected static ?string $recordTitleAttribute = 'price';
    
    protected static ?string $title = 'الأسعار';
    
    protected static ?string $modelLabel = 'سعر';
    
    protected static ?string $pluralModelLabel = 'الأسعار';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('price_list_id')
                    ->label('قائمة الأسعار')
                    ->options(PriceList::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload(),
                    
                Forms\Components\Select::make('uom_id')
                    ->label('وحدة القياس')
                    ->options(function () {
                        $product = $this->getOwnerRecord();
                        if ($product && $product->default_uom_id) {
                            return UOM::where('id', $product->default_uom_id)
                                ->orWhere('base_uom_id', $product->default_uom_id)
                                ->pluck('name', 'id');
                        }
                        return UOM::where('is_active', true)->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->default(function () {
                        $product = $this->getOwnerRecord();
                        return $product?->default_uom_id;
                    }),
                    
                Forms\Components\TextInput::make('price')
                    ->label('السعر')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                    
                Forms\Components\TextInput::make('min_quantity')
                    ->label('الحد الأدنى للكمية')
                    ->numeric()
                    ->default(1),
                    
                Forms\Components\TextInput::make('discount_percentage')
                    ->label('نسبة الخصم (%)')
                    ->numeric()
                    ->default(0)
                    ->suffix('%'),
                    
                Forms\Components\DatePicker::make('valid_from')
                    ->label('صالح من')
                    ->default(now()),
                    
                Forms\Components\DatePicker::make('valid_until')
                    ->label('صالح حتى'),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('priceList.name')
                    ->label('قائمة الأسعار')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('uom.symbol')
                    ->label('الوحدة')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('الخصم')
                    ->suffix('%'),
                    
                Tables\Columns\TextColumn::make('final_price')
                    ->label('السعر بعد الخصم')
                    ->money('USD')
                    ->getStateUsing(fn ($record) => $record->final_price),
                    
                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('الحد الأدنى'),
                    
                Tables\Columns\IconColumn::make('is_valid')
                    ->label('ساري')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->is_valid),
                    
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('صالح من')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('صالح حتى')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('price_list_id')
                    ->label('قائمة الأسعار')
                    ->options(PriceList::where('is_active', true)->pluck('name', 'id')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة سعر')
                    ->mutateFormDataBeforeCreate(function (array $data): array {
                        // التأكد من وجود uom_id
                        if (!isset($data['uom_id']) || empty($data['uom_id'])) {
                            $product = $this->getOwnerRecord();
                            if ($product && $product->default_uom_id) {
                                $data['uom_id'] = $product->default_uom_id;
                            }
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}