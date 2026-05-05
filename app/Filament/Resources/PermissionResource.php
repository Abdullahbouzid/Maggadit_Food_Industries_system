<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'الصلاحيات والأدوار';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'صلاحية';
    protected static ?string $pluralModelLabel = 'الصلاحيات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الصلاحية')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الصلاحية')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: view_users, create_products')
                            ->helperText('يجب أن يكون اسم الصلاحية فريداً ويصف الإذن'),
                            
                        Forms\Components\TextInput::make('display_name')
                            ->label('الاسم المعروض')
                            ->maxLength(255)
                            ->placeholder('مثال: عرض المستخدمين')
                            ->helperText('الاسم الذي سيظهر في واجهة المستخدم'),
                            
                        Forms\Components\TextInput::make('description')
                            ->label('الوصف')
                            ->maxLength(255)
                            ->placeholder('شرح مختصر للصلاحية'),
                            
                        Forms\Components\Select::make('guard_name')
                            ->label('نوع الحماية')
                            ->options([
                                'web' => 'Web',
                                'api' => 'API',
                            ])
                            ->default('web')
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('نظام الحماية المستخدم للصلاحية'),
                            
                        Forms\Components\Toggle::make('is_system')
                            ->label('صلاحية نظامية')
                            ->default(false)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('الصلاحيات النظامية لا يمكن حذفها'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الصلاحية')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('display_name')
                    ->label('الاسم المعروض')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('نوع الحماية')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('roles_count')
                    ->label('عدد الأدوار')
                    ->counts('roles')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_system')
                    ->label('نظامية')
                    ->boolean()
                    ->trueIcon('heroicon-o-cog')
                    ->falseIcon('heroicon-o-user'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('نوع الحماية')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('صلاحية نظامية'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('عرض'),
                    Tables\Actions\EditAction::make()
                        ->label('تعديل'),
                    Tables\Actions\DeleteAction::make()
                        ->label('حذف')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => !$record->is_system),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحددين')
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            PermissionResource\RelationManagers\RolesRelationManager::class,
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            // 'view' => Pages\ViewPermission::route('/{record}'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}