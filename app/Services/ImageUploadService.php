<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * アップロード画像の保存。**画像の保存は必ずこのサービスを通すこと。**
 *
 * - スマホで撮影した写真は EXIF に回転情報を持つ。そのまま保存すると横倒しになるため、
 *   向きを補正してから保存する(介護事業所の担当者が現場で撮影する想定)。
 * - 表示速度のため WebP に変換し、長辺を上限まで縮小する。
 * - 保存先は uploads ディスク(public/uploads)。
 *   storage:link のシンボリックリンクに依存しない(config/filesystems.php を参照)。
 */
class ImageUploadService
{
    public const DISK = 'uploads';

    /** 共有レンタルサーバのメモリ上限を考慮した長辺の上限。 */
    private const MAX_DIMENSION = 1600;

    private const QUALITY = 80;

    public function __construct(private readonly ImageManager $manager) {}

    /**
     * GD が WebP を扱えるか。
     *
     * PHP 標準ビルドには含まれるが、ビルドオプション次第では欠けることがある
     * (実際にローカルの Docker で欠けていて画像保存が落ちた)。
     * 環境構築時に必ず確認すること。
     */
    public static function supportsWebp(): bool
    {
        return function_exists('imagewebp');
    }

    /**
     * 画像を WebP に変換して保存し、ディスク内の相対パスを返す。
     *
     * @param  string  $directory  uploads ディスク内のディレクトリ(例: companies/logos)
     * @param  string|null  $replacing  置き換える既存ファイルのパス。あれば削除する
     */
    public function store(UploadedFile $file, string $directory, ?string $replacing = null): string
    {
        $image = $this->manager->decodePath($file->getRealPath());

        // EXIF の回転情報を反映してから縮小する
        $image->orient();

        // 縦横比を保ったまま、長辺が上限を超える場合だけ縮小する
        $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);

        // ULID はソート可能で衝突しない。連番と違い他社のファイル名を推測されにくい。
        $path = trim($directory, '/').'/'.Str::ulid()->toBase32().'.webp';

        $encoded = $image->encode(new WebpEncoder(quality: self::QUALITY));

        Storage::disk(self::DISK)->put($path, (string) $encoded);

        if ($replacing) {
            $this->delete($replacing);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * 表示用の URL。画像が無ければ null を返す。
     */
    public static function url(?string $path): ?string
    {
        return $path ? Storage::disk(self::DISK)->url($path) : null;
    }
}
