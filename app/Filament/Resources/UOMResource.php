<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UOMResource\Pages;
use App\Models\UOM;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class UOMResource extends Resource
{
    protected static ?string $model = UOM::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'إدارة المنتجات';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'وحدة قياس';
    protected static ?string $pluralModelLabel = 'وحدات القياس';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات وحدة القياس')
                    ->schema([
                        TextInput::make('code')
                            ->label('الكود')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: KG, PC, BOX')
                            ->helperText('كود فريد للوحدة'),
                            
                        TextInput::make('name')
                            ->label('اسم الوحدة')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: كيلوجرام, قطعة, صندوق'),
                            
                        TextInput::make('symbol')
                            ->label('الرمز')
                            ->maxLength(10)
                            ->placeholder('مثال: كجم, قط, صندوق')
                            ->helperText('الرمز المختصر للوحدة'),
                            
                        Select::make('base_uom_id')
                            ->label('الوحدة الأساسية')
                            ->options(UOM::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->helperText('إذا كانت هذه وحدة فرعية، اختر الوحدة الأساسية'),
                            
                        TextInput::make('conversion_factor')
                            ->label('معامل التحويل')
                            ->numeric()
                            ->step(0.0001)
                            ->default(1)
                            ->helperText('كم وحدة أساسية تساوي هذه الوحدة')
                            ->visible(fn ($get) => $get('base_uom_id') !== null),
                            
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
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('symbol')
                    ->label('الرمز')
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('baseUom.name')
                    ->label('الوحدة الأساسية')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($state) => $state ?? 'وحدة أساسية'),
                    
                TextColumn::make('conversion_factor')
                    ->label('معامل التحويل')
                    ->numeric(4)
                    ->toggleable(),
                    
                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),
                    
                TextColumn::make('child_uoms_count')
                    ->label('الوحدات الفرعية')
                    ->counts('childUoms')
                    ->badge()
                    ->color('warning'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة'),
                Tables\Filters\SelectFilter::make('base_uom_id')
                    ->label('الوحدة الأساسية')
                    ->relationship('baseUom', 'name'),
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
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUOMS::route('/'),
            'create' => Pages\CreateUOM::route('/create'),
            'edit' => Pages\EditUOM::route('/{record}/edit'),
        ];
    }
}