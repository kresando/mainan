<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Post;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestPosts extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    // Mengaktifkan lazy loading untuk widget
    protected static bool $isLazy = true;
    
    // Membatasi jumlah record yang dimuat per halaman
    protected int | string | null $defaultTableRecordsPerPageSelectOption = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Post::query()
                    ->select(['id', 'title', 'created_at', 'category_id']) // Hanya ambil kolom yang diperlukan
                    ->with(['category:id,name']) // Eager loading untuk relasi
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50), // Membatasi panjang teks untuk performa
                
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->contentFooter(fn () => 'Showing last 5 posts')
            ->poll('30s'); // Refresh data setiap 30 detik alih-alih default 10 detik
    }
} 