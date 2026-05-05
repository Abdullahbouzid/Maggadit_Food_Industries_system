<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceListResource\Pages;
use App\Models\PriceList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'قوائم الأسعار';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'قائمة أسعار';
    protected static ?string $pluralModelLabel = 'قوائم الأسعار';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات قائمة الأسعار')
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: PL-WHOLESALE-01'),
                            
                        TextInput::make('name')
                            ->label('اسم القائمة')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: قائمة أسعار الجملة'),
                            
                        Textarea::make('description')
                            ->label('الوصف')
                            ->maxLength(65535)
                            ->rows(2),
                            
                        Select::make('type')
                            ->label('نوع القائمة')
                            ->options([
                                'wholesale' => 'جملة',
                                'retail' => 'تجزئة',
                                'distributor' => 'موزع',
                                'special' => 'خاص',
                            ])
                            ->required()
                            ->default('retail'),
                            
                        DatePicker::make('valid_from')
                            ->label('صالح من')
                            ->displayFormat('Y-m-d')
                            ->default(now()),
                            
                        DatePicker::make('valid_until')
                            ->label('صالح حتى')
                            ->displayFormat('Y-m-d')
                            ->after('valid_from')
                            ->helperText('اتركه فارغاً إذا كان ساري بشكل دائم'),
                            
                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ])->columns(2),
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
                    ->label('اسم القائمة')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'wholesale' => 'جملة',
                        'retail' => 'تجزئة',
                        'distributor' => 'موزع',
                        'special' => 'خاص',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'wholesale',
                        'info' => 'retail',
                        'warning' => 'distributor',
                        'danger' => 'special',
                    ]),
                    
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->toggleable(),
                    
                BadgeColumn::make('status_text')
                    ->label('الحالة')
                    ->getStateUsing(fn ($record) => $record->status_text)
                    ->colors([
                        'success' => 'نشط',
                        'danger' => 'غير نشط',
                        'warning' => 'منتهي',
                    ]),
                    
                TextColumn::make('valid_from')
                    ->label('صالح من')
                    ->date()
                    ->toggleable(),
                    
                TextColumn::make('valid_until')
                    ->label('صالح حتى')
                    ->date()
                    ->toggleable(),
                    
                TextColumn::make('prices_count')
                    ->label('عدد الأسعار')
                    ->counts('prices')
                    ->badge()
                    ->color('info'),
                    
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'wholesale' => 'جملة',
                        'retail' => 'تجزئة',
                        'distributor' => 'موزع',
                        'special' => 'خاص',
                    ]),
                    
                TernaryFilter::make('is_active')
                    ->label('نشط')
                    ->boolean(),
                    
                // Filter::make('valid')
                //     ->label('السارية فقط')
                //     ->query(fn ($query) => $query->valid()),
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
            PriceListResource\RelationManagers\ProductsRelationManager::class,
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'view' => Pages\ViewPriceList::route('/{record}'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}