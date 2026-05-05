<?php

namespace App\Filament\Resources\WarehouseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InventoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';
    
    protected static ?string $recordTitleAttribute = 'batch_number';
    
    protected static ?string $title = 'المخزون';
    
    protected static ?string $modelLabel = 'مخزون';
    
    protected static ?string $pluralModelLabel = 'المخزون';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('المنتج')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\Select::make('uom_id')
                    ->label('وحدة القياس')
                    ->relationship('uom', 'name')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('batch_number')
                    ->label('رقم الدفعة'),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('تاريخ الصلاحية'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('batch_number')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('المنتج')
                    ->searchable(),
                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU'),
                Tables\Columns\TextColumn::make('batch_number')
                    ->label('رقم الدفعة'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('المتاح')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('تاريخ الصلاحية')
                    ->date(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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