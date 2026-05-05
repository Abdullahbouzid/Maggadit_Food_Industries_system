<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'الصلاحيات والأدوار';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'دور';
    protected static ?string $pluralModelLabel = 'الأدوار';
    
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الدور')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الدور')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('مثال: admin, manager, user'),
                            
                        Forms\Components\TextInput::make('guard_name')
                            ->label('نوع الحماية')
                            ->default('web')
                            ->required()
                            ->disabled()
                            ->helperText('نظام الحماية المستخدم للدور'),
                    ])->columns(2),
                    
                Forms\Components\Section::make('الصلاحيات')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->label('')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->gridDirection('row')
                            ->searchable()
                            ->bulkToggleable()
                            ->options(function () {
                                $options = [];
                                $permissions = Permission::orderBy('name')->get();
                                foreach ($permissions as $permission) {
                                    $options[$permission->name] = $permission->name;
                                }
                                return $options;
                            }),
                    ]),
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
                    ->label('اسم الدور')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'user_manager' => 'info',
                        'product_manager' => 'success',
                        'warehouse_manager' => 'primary',
                        'sales_manager' => 'secondary',
                        'viewer' => 'gray',
                        default => 'primary',
                    }),
                    
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('نوع الحماية')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('عدد الصلاحيات')
                    ->counts('permissions')
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('users_count')
                    ->label('عدد المستخدمين')
                    ->counts('users')
                    ->badge()
                    ->color('warning'),
                    
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
                        ->visible(fn ($record) => !in_array($record->name, ['super_admin', 'admin'])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحددين'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RoleResource\RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            // 'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}