<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Textarea;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Hash;
use BezhanSalleh\FilamentShield\Resources\Concerns\ShieldedResource;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'إدارة النظام';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمين';
    
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
                Section::make('معلومات المستخدم الأساسية')
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل الاسم الكامل'),
                            
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('example@domain.com'),
                            
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('05XXXXXXXX'),
                            
                        Textarea::make('address')
                            ->label('العنوان')
                            ->maxLength(65535)
                            ->rows(2),
                    ])->columns(2),
                    
                Section::make('معلومات الدخول والصلاحيات')
                    ->schema([
                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->helperText('اتركه فارغاً إذا لم تريد تغييره'),
                            
                        Select::make('roles')
                            ->label('الأدوار')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('يمكن اختيار أكثر من دور للمستخدم'),
                            
                        Select::make('warehouse_id')
                            ->label('المخزن المرتبط')
                            ->options(Warehouse::where('is_active', true)->pluck('name', 'id'))
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->helperText('المخزن الذي يعمل به المستخدم'),
                            
                        DateTimePicker::make('email_verified_at')
                            ->label('تاريخ توثيق البريد')
                            ->nullable()
                            ->displayFormat('Y-m-d H:i:s'),
                            
                        Toggle::make('is_active')
                            ->label('حساب نشط')
                            ->default(true)
                            ->helperText('تعطيل الحساب يمنع المستخدم من الدخول'),
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
                    
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user'),
                    
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                    
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->toggleable(),
                    
                BadgeColumn::make('role_names')
                    ->label('الأدوار')
                    ->getStateUsing(fn ($record) => $record->role_names)
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'admin',
                        'success' => 'sales_manager',
                        'info' => 'warehouse_manager',
                    ])
                    ->separator(',')
                    ->toggleable(),
                    
                TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->toggleable()
                    ->badge()
                    ->color('info'),
                    
                IconColumn::make('email_verified_at')
                    ->label('توثيق البريد')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => !is_null($record->email_verified_at)),
                    
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                    
                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('الأدوار')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                    
                SelectFilter::make('warehouse_id')
                    ->label('المخزن')
                    ->relationship('warehouse', 'name'),
                    
                TernaryFilter::make('is_active')
                    ->label('الحساب نشط')
                    ->boolean()
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->placeholder('الكل'),
                    
                TernaryFilter::make('email_verified_at')
                    ->label('البريد موثق')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                    ),
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
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('verify_email')
                    ->label('توثيق البريد')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['email_verified_at' => now()]))
                    ->visible(fn ($record) => is_null($record->email_verified_at)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف المحددين'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحددين')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('تعطيل المحددين')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->searchable();
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}