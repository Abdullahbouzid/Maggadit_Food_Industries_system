<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Warehouse;
use App\Models\UOM;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';
    protected static ?string $recordTitleAttribute = 'batch_number';
    protected static ?string $modelLabel = 'مخزون';
    protected static ?string $pluralModelLabel = 'المخزون';
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المخزون')
                    ->schema([
                        Select::make('warehouse_id')
                            ->label('المخزن')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Select::make('uom_id')
                            ->label('وحدة القياس')
                            ->options(function () {
                                $product = $this->getOwnerRecord();
                                return UOM::where('is_active', true)
                                    ->when($product->default_uom_id, function ($query) use ($product) {
                                        $query->where('id', $product->default_uom_id)
                                            ->orWhere('base_uom_id', $product->default_uom_id);
                                    })
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        TextInput::make('batch_number')
                            ->label('رقم الدفعة')
                            ->maxLength(255)
                            ->placeholder('مثال: BATCH-2024-001'),
                            
                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->step(0.0001)
                            ->rules(['min:0']),
                            
                        TextInput::make('reserved_quantity')
                            ->label('الكمية المحجوزة')
                            ->numeric()
                            ->default(0)
                            ->step(0.0001)
                            ->rules(['min:0'])
                            ->helperText('كمية محجوزة للطلبات المعلقة'),
                            
                        DatePicker::make('production_date')
                            ->label('تاريخ الإنتاج')
                            ->displayFormat('Y-m-d'),
                            
                        DatePicker::make('expiry_date')
                            ->label('تاريخ الصلاحية')
                            ->displayFormat('Y-m-d')
                            ->after('production_date')
                            ->helperText('تاريخ انتهاء صلاحية المنتج'),
                    ])->columns(2),
            ]);
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('uom.symbol')
                    ->label('الوحدة')
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('batch_number')
                    ->label('رقم الدفعة')
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                    
                TextColumn::make('reserved_quantity')
                    ->label('محجوز')
                    ->numeric(2)
                    ->color('warning'),
                    
                TextColumn::make('available_quantity')
                    ->label('متاح')
                    ->numeric(2)
                    ->getStateUsing(fn ($record) => $record->available_quantity)
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                    
                BadgeColumn::make('expiry_date')
                    ->label('تاريخ الصلاحية')
                    ->date()
                    ->color(fn ($state) => 
                        $state && $state->isPast() ? 'danger' : 
                        ($state && $state->diffInDays(now()) <= 30 ? 'warning' : 'success')
                    )
                    ->icon(fn ($state) => 
                        $state && $state->isPast() ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle'
                    ),
                    
                BadgeColumn::make('expiry_status_text')
                    ->label('حالة الصلاحية')
                    ->getStateUsing(fn ($record) => $record->expiry_status_text)
                    ->color(fn ($record) => $record->expiry_status_color),
                    
                TextColumn::make('days_until_expiry')
                    ->label('أيام للصلاحية')
                    ->numeric(0)
                    ->color(fn ($state) => 
                        $state <= 0 ? 'danger' : 
                        ($state <= 30 ? 'warning' : 'success')
                    ),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('المخزن')
                    ->options(Warehouse::where('is_active', true)->pluck('name', 'id')),
                    
                Filter::make('expired')
                    ->label('منتهي الصلاحية')
                    ->query(fn (Builder $query): Builder => $query->where('expiry_date', '<', now())->where('quantity', '>', 0)),
                    
                Filter::make('expiring_soon')
                    ->label('ينتهي قريباً (30 يوم)')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('expiry_date', [now(), now()->addDays(30)])->where('quantity', '>', 0)),
                    
                Filter::make('low_stock')
                    ->label('مخزون منخفض (< 10)')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '<', 10)->where('quantity', '>', 0)),
                    
                Filter::make('out_of_stock')
                    ->label('نفذ من المخزون')
                    ->query(fn (Builder $query): Builder => $query->where('quantity', '<=', 0)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('تعديل'),
                Tables\Actions\DeleteAction::make()
                    ->label('حذف')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expiry_date', 'asc');
    }
}