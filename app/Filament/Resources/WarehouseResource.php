<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TernaryFilter;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'المخزون والصلاحية';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'مخزن';
    protected static ?string $pluralModelLabel = 'المخازن';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المخزن')
                    ->schema([
                        TextInput::make('code')
                            ->label('كود المخزن')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: WH-001'),
                            
                        TextInput::make('name')
                            ->label('اسم المخزن')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: المخزن الرئيسي'),
                            
                        Textarea::make('address')
                            ->label('العنوان')
                            ->required()
                            ->maxLength(65535)
                            ->rows(2),
                            
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('مثال: 05XXXXXXXX'),
                            
                        TextInput::make('manager_name')
                            ->label('اسم المدير')
                            ->maxLength(255)
                            ->placeholder('اسم مسؤول المخزن'),
                            
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])->columns(2),
                    
                Forms\Components\Section::make('إحصائيات المخزن')
                    ->schema([
                        Forms\Components\Placeholder::make('total_items')
                            ->label('إجمالي العناصر')
                            ->content(fn ($record) => $record ? number_format($record->total_items, 2) . ' وحدة' : 'لم يتم الحفظ بعد'),
                            
                        Forms\Components\Placeholder::make('total_value')
                            ->label('القيمة الإجمالية')
                            ->content(fn ($record) => $record ? '$' . number_format($record->total_value, 2) : 'لم يتم الحفظ بعد'),
                            
                        Forms\Components\Placeholder::make('expired_products_count')
                            ->label('المنتجات منتهية الصلاحية')
                            ->content(fn ($record) => $record ? $record->expired_products->count() . ' منتج' : 'لم يتم الحفظ بعد'),
                    ])->columns(3)
                    ->visible(fn ($record) => !is_null($record)),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('name')
                    ->label('اسم المخزن')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('address')
                    ->label('العنوان')
                    ->limit(30)
                    ->toggleable(),
                    
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->toggleable(),
                    
                TextColumn::make('manager_name')
                    ->label('المدير'),
                    
                TextColumn::make('total_items')
                    ->label('إجمالي العناصر')
                    ->numeric(0)
                    ->sortable()
                    ->color('info'),
                    
                TextColumn::make('total_value')
                    ->label('القيمة الإجمالية')
                    ->money('USD')
                    ->sortable()
                    ->color('success'),
                    
                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
              WarehouseResource\RelationManagers\InventoriesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'view' => Pages\ViewWarehouse::route('/{record}'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}