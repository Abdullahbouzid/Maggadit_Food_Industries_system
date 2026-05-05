<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\UOM;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'إدارة المنتجات';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'منتج';
    protected static ?string $pluralModelLabel = 'المنتجات';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('المعلومات الأساسية')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU (رقم المنتج)')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->placeholder('مثال: PRD-001')
                                    ->helperText('رقم فريد للمنتج'),
                                    
                                TextInput::make('barcode')
                                    ->label('الباركود')
                                    ->maxLength(50)
                                    ->placeholder('مثال: 1234567890123'),
                            ]),
                            
                        TextInput::make('name')
                            ->label('اسم المنتج')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل اسم المنتج'),
                            
                        Textarea::make('description')
                            ->label('الوصف')
                            ->maxLength(65535)
                            ->rows(3),
                            
                        Textarea::make('technical_specs')
                            ->label('المواصفات الفنية')
                            ->rows(4),
                    ]),
                    
                Section::make('التصنيفات')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('category_id')
                                    ->label('الفئة')
                                    ->options(Category::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                    
                                Select::make('brand_id')
                                    ->label('البراند')
                                    ->options(Brand::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                    
                                Select::make('default_uom_id')
                                    ->label('وحدة القياس الأساسية')
                                    ->options(UOM::where('is_active', true)->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->helperText('الوحدة الرئيسية للمنتج'),
                            ]),
                    ]),
                    
                Section::make('الأبعاد والوزن')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('weight')
                                    ->label('الوزن (كجم)')
                                    ->numeric()
                                    ->step(0.001)
                                    ->suffix('كجم'),
                                    
                                TextInput::make('length')
                                    ->label('الطول (سم)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('سم'),
                                    
                                TextInput::make('width')
                                    ->label('العرض (سم)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('سم'),
                                    
                                TextInput::make('height')
                                    ->label('الارتفاع (سم)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('سم'),
                                    // In ProductResource.php, modify the attach action
Tables\Actions\AttachAction::make()
    ->recordSelectOptionsQuery(function ($query) {
        return $query; // Remove any priceLists() calls
    }),
                            ]),
                    ]),
                    
                Section::make('الحالة والإتاحة')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('منتج نشط')
                                    ->default(true),
                                    
                                Toggle::make('is_available_for_order')
                                    ->label('متاح للطلب')
                                    ->default(true),
                            ]),
                    ]),
                    
                Section::make('معلومات المخزون والأسعار')
                    ->schema([
                        Placeholder::make('total_stock')
                            ->label('إجمالي المخزون')
                            ->content(fn ($record) => $record ? number_format($record->total_stock, 2) . ' ' . ($record->defaultUom?->symbol ?? '') : 'لم يتم الحفظ بعد')
                            ->visible(fn ($record) => !is_null($record)),
                            
                        Placeholder::make('available_stock')
                            ->label('المخزون المتاح')
                            ->content(fn ($record) => $record ? number_format($record->available_stock, 2) . ' ' . ($record->defaultUom?->symbol ?? '') : 'لم يتم الحفظ بعد')
                            ->visible(fn ($record) => !is_null($record)),
                    ])->columns(2),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->copyable(),
                    
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                    
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('brand.name')
                    ->label('البراند')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                    
                TextColumn::make('defaultUom.symbol')
                    ->label('الوحدة')
                    ->badge()
                    ->color('warning'),
                    
                BadgeColumn::make('total_stock')
                    ->label('المخزون الكلي')
                    ->numeric(2)
                    ->color(fn ($state) => $state > 100 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->icon(fn ($state) => $state > 0 ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                    
                TextColumn::make('available_stock')
                    ->label('المتاح')
                    ->numeric(2)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                    
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                    
                IconColumn::make('is_available_for_order')
                    ->label('متاح للطلب')
                    ->boolean()
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('brand_id')
                    ->label('البراند')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                    
                TernaryFilter::make('is_active')
                    ->label('منتج نشط')
                    ->boolean(),
                    
                TernaryFilter::make('is_available_for_order')
                    ->label('متاح للطلب')
                    ->boolean(),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label('منتجات منخفضة المخزون')
                    ->query(fn (Builder $query): Builder => $query->whereHas('inventories', function ($q) {
                        $q->havingRaw('SUM(quantity) < 10');
                    })),
                    
                Tables\Filters\Filter::make('out_of_stock')
                    ->label('منتجات نفذت من المخزون')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('inventories', function ($q) {
                        $q->where('quantity', '>', 0);
                    })),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('عرض')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable();
    }
    
    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryRelationManager::class,
            RelationManagers\PricesRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}