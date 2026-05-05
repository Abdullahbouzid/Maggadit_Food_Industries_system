<?php

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\UOM;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    
    protected static ?string $recordTitleAttribute = 'name';
    
    protected static ?string $title = 'المنتجات والأسعار';
    
    protected static ?string $modelLabel = 'منتج';
    
    protected static ?string $pluralModelLabel = 'المنتجات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('المنتج')
                    ->options(Product::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product && $product->default_uom_id) {
                                $set('uom_id', $product->default_uom_id);
                            }
                        }
                    }),
                    
                Select::make('uom_id')
                    ->label('وحدة القياس')
                    ->options(function (callable $get) {
                        $productId = $get('product_id');
                        if ($productId) {
                            $product = Product::find($productId);
                            if ($product && $product->default_uom_id) {
                                return UOM::where('id', $product->default_uom_id)
                                    ->orWhere('base_uom_id', $product->default_uom_id)
                                    ->pluck('name', 'id');
                            }
                        }
                        return UOM::where('is_active', true)->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->helperText('وحدة القياس لهذا السعر'),
                    
                TextInput::make('price')
                    ->label('السعر')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->minValue(0),
                    
                TextInput::make('min_quantity')
                    ->label('الحد الأدنى للكمية')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->helperText('أقل كمية يمكن شراؤها بهذا السعر'),
                    
                TextInput::make('discount_percentage')
                    ->label('نسبة الخصم (%)')
                    ->numeric()
                    ->default(0)
                    ->suffix('%')
                    ->step(0.01)
                    ->minValue(0)
                    ->maxValue(100),
                    
                DatePicker::make('valid_from')
                    ->label('صالح من')
                    ->default(now())
                    ->displayFormat('Y-m-d'),
                    
                DatePicker::make('valid_until')
                    ->label('صالح حتى')
                    ->displayFormat('Y-m-d')
                    ->after('valid_from')
                    ->helperText('اتركه فارغاً إذا كان ساري بشكل دائم'),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('pivot.uom.symbol')
                    ->label('الوحدة')
                    ->getStateUsing(fn ($record) => $record->pivot->uom->symbol ?? '-')
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('pivot.price')
                    ->label('السعر')
                    ->money('USD')
                    ->sortable(),
                    
                TextColumn::make('pivot.discount_percentage')
                    ->label('الخصم')
                    ->suffix('%')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'secondary'),
                    
                TextColumn::make('pivot.final_price')
                    ->label('السعر بعد الخصم')
                    ->money('USD')
                    ->getStateUsing(fn ($record) => $record->pivot->price * (1 - $record->pivot->discount_percentage / 100))
                    ->color('warning'),
                    
                TextColumn::make('pivot.min_quantity')
                    ->label('الحد الأدنى'),
                    
                IconColumn::make('is_valid')
                    ->label('ساري')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $this->isPriceValid($record->pivot))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                    
                TextColumn::make('pivot.valid_from')
                    ->label('صالح من')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('pivot.valid_until')
                    ->label('صالح حتى')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('valid_only')
                    ->label('الأسعار السارية فقط')
                    ->query(fn ($query) => $query->whereHas('prices', function ($q) {
                        $q->where(function ($sub) {
                            $sub->whereNull('valid_from')
                                ->orWhere('valid_from', '<=', now());
                        })->where(function ($sub) {
                            $sub->whereNull('valid_until')
                                ->orWhere('valid_until', '>=', now());
                        });
                    })),
                    
                Tables\Filters\Filter::make('has_discount')
                    ->label('عليها خصم')
                    ->query(fn ($query) => $query->whereHas('prices', function ($q) {
                        $q->where('discount_percentage', '>', 0);
                    })),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة منتج')
                    ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                        $priceList = $this->getOwnerRecord();
                        
                        $priceList->products()->attach($data['product_id'], [
                            'uom_id' => $data['uom_id'] ?? $data['product_default_uom_id'] ?? null,
                            'price' => $data['price'],
                            'min_quantity' => $data['min_quantity'] ?? 1,
                            'discount_percentage' => $data['discount_percentage'] ?? 0,
                            'valid_from' => $data['valid_from'] ?? now(),
                            'valid_until' => $data['valid_until'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        return Product::find($data['product_id']);
                    })
                    ->form([
                        Select::make('product_id')
                            ->label('المنتج')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $product = Product::find($state);
                                    if ($product) {
                                        $set('product_default_uom_id', $product->default_uom_id);
                                        $set('uom_id', $product->default_uom_id);
                                    }
                                }
                            }),
                        Forms\Components\Hidden::make('product_default_uom_id'),
                        Select::make('uom_id')
                            ->label('وحدة القياس')
                            ->options(function (callable $get) {
                                $productId = $get('product_id');
                                if ($productId) {
                                    $product = Product::find($productId);
                                    if ($product && $product->default_uom_id) {
                                        return UOM::where('id', $product->default_uom_id)
                                            ->orWhere('base_uom_id', $product->default_uom_id)
                                            ->pluck('name', 'id');
                                    }
                                }
                                return UOM::where('is_active', true)->pluck('name', 'id');
                            })
                            ->required(),
                        TextInput::make('price')
                            ->label('السعر')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                        TextInput::make('min_quantity')
                            ->label('الحد الأدنى للكمية')
                            ->numeric()
                            ->default(1),
                        TextInput::make('discount_percentage')
                            ->label('نسبة الخصم')
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),
                        DatePicker::make('valid_from')
                            ->label('صالح من')
                            ->default(now()),
                        DatePicker::make('valid_until')
                            ->label('صالح حتى'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل')
                    ->form([
                        Select::make('uom_id')
                            ->label('وحدة القياس')
                            ->options(function ($record) {
                                $product = $record;
                                if ($product && $product->default_uom_id) {
                                    return UOM::where('id', $product->default_uom_id)
                                        ->orWhere('base_uom_id', $product->default_uom_id)
                                        ->pluck('name', 'id');
                                }
                                return UOM::where('is_active', true)->pluck('name', 'id');
                            })
                            ->required(),
                        TextInput::make('price')
                            ->label('السعر')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('min_quantity')
                            ->label('الحد الأدنى')
                            ->numeric(),
                        TextInput::make('discount_percentage')
                            ->label('الخصم')
                            ->numeric()
                            ->suffix('%'),
                        DatePicker::make('valid_from')
                            ->label('صالح من'),
                        DatePicker::make('valid_until')
                            ->label('صالح حتى'),
                    ])
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        $pivot = $record->pivot;
                        $data['uom_id'] = $pivot->uom_id;
                        $data['price'] = $pivot->price;
                        $data['min_quantity'] = $pivot->min_quantity;
                        $data['discount_percentage'] = $pivot->discount_percentage;
                        $data['valid_from'] = $pivot->valid_from;
                        $data['valid_until'] = $pivot->valid_until;
                        return $data;
                    })
                    ->using(function (array $data, $record): void {
                        $record->pivot->update([
                            'uom_id' => $data['uom_id'],
                            'price' => $data['price'],
                            'min_quantity' => $data['min_quantity'] ?? 1,
                            'discount_percentage' => $data['discount_percentage'] ?? 0,
                            'valid_from' => $data['valid_from'] ?? null,
                            'valid_until' => $data['valid_until'] ?? null,
                        ]);
                    }),
                Tables\Actions\DetachAction::make()
                    ->label('إزالة'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('إزالة المحددين'),
                ]),
            ]);
    }
    
    protected function isPriceValid($pivot): bool
    {
        $now = now();
        
        // تحويل القيم إلى كائنات Carbon إذا كانت نصوصاً
        $validFrom = $pivot->valid_from;
        $validUntil = $pivot->valid_until;
        
        // التحقق من valid_from
        $validFromCheck = true;
        if ($validFrom) {
            if (is_string($validFrom)) {
                $validFrom = Carbon::parse($validFrom);
            }
            $validFromCheck = $validFrom->lte($now);
        }
        
        // التحقق من valid_until
        $validUntilCheck = true;
        if ($validUntil) {
            if (is_string($validUntil)) {
                $validUntil = Carbon::parse($validUntil);
            }
            $validUntilCheck = $validUntil->gte($now);
        }
        
        return $validFromCheck && $validUntilCheck;
    }
}