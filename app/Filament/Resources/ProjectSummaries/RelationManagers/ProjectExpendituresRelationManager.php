<?php

namespace App\Filament\Resources\ProjectSummaries\RelationManagers;

use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use App\Models\ProjectExpenditure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\RelationManagers\RelationManager;

class ProjectExpendituresRelationManager extends RelationManager
{
  protected static string $relationship = 'expenditures';

  protected static ?string $title = 'Material and Labour Expenditure';

  public function isReadOnly(): bool
  {
    return false;
  }

  public function form(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Detail Pengeluaran')
        ->description('Masukan info pengeluaran di bawah ini.')
        ->icon('heroicon-o-receipt-percent')
        ->schema([
          Select::make('type')
            ->label('Tipe Pengeluaran')
            ->options([
              ProjectExpenditure::TYPE_MATERIAL => 'Material',
              ProjectExpenditure::TYPE_LABOUR => 'Labour',
            ])
            ->native(false)
            ->required()
            ->helperText('Choose whether this expense is Material or Labour.'),

          DatePicker::make('expenditure_date')
            ->label('Tanggal Pengeluaran')
            ->required()
            ->default(now())
            ->native(false),

          TextInput::make('description')
            ->label('Description')
            ->placeholder('e.g. Cable NYY 4×10 mm, Installation Labour')
            ->required()
            ->maxLength(255)
            ->columnSpanFull(),

          TextInput::make('amount')
            ->label('Nilai Pengeluaran')
            ->prefix('Rp')
            ->placeholder('0')
            ->mask(RawJs::make('$money($input)'))
            ->stripCharacters(',')
            ->numeric()
            ->minValue(0)
            ->required()
            ->helperText('Enter the amount in Rupiah. Example: Rp 1,500,000.')
            ->columnSpanFull(),

          FileUpload::make('attachment')
            ->label('Lampiran')
            ->disk('public')
            ->directory('project-expenditures')
            ->acceptedFileTypes([
              'application/pdf',
              'image/*',
            ])
            ->maxSize(20480)
            ->downloadable()
            ->openable()
            ->helperText('Opsional. Unggah gambar atau PDF, maksimal 20MB.')
            ->columnSpanFull(),
        ])
        ->columns(2),
    ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('type')
          ->label('Jenis')
          ->badge()
          ->sortable(),

        TextColumn::make('description')
          ->label('Deskripsi')
          ->searchable()
          ->sortable()
          ->wrap(),

        TextColumn::make('amount')
          ->label('Nilai Pengeluaran')
          ->formatStateUsing(
            fn($state): string =>
              'Rp ' . number_format((float) $state, 0, ',', '.')
          )
          ->sortable()
          ->alignRight(),

        TextColumn::make('expenditure_date')
          ->label('Tanggal Pengeluaran')
          ->date('d M Y')
          ->sortable(),

        TextColumn::make('attachment')
          ->label('Lampiran')
          ->formatStateUsing(fn(?string $state): string => $state ? 'Lihat file' : '-')
          ->url(fn(?string $state): ?string => $state ? Storage::url($state) : null)
          ->openUrlInNewTab()
          ->color('primary'),
      ])
      ->defaultSort('expenditure_date', 'desc')
      ->headerActions([
        CreateAction::make(),
      ])
      ->recordActions([
        EditAction::make(),
        DeleteAction::make(),
      ]);
  }
}