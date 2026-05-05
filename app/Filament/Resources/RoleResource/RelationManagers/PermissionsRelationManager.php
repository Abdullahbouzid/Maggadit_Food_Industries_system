<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = 'الصلاحيات';
    protected static ?string $modelLabel = 'صلاحية';
    protected static ?string $pluralModelLabel = 'الصلاحيات';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('permission_id')
                    ->label('الصلاحية')
                    ->options(Permission::orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الصلاحية')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('نوع الحماية')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->date(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('إضافة صلاحية')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('إزالة'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}