<?php

namespace TadaTsu\GDContentsGenerator;

class Generator
{
    /**
     * background colors
     *
     * @var array
     */
    public $colors = [
        100   => [0xf3, 0xeb, 0xc4, false],
        500   => [0xf1, 0x54, 0x5d, true],
        1000  => [0xb2, 0xe2, 0xee, false],
        2500  => [0x5c, 0xc2, 0x78, true],
        5000  => [0x3c, 0xba, 0xec, true],
        10000 => [0x3f, 0x3f, 0x3f, true],
    ];

    public function contents(Request $request)
    {

        $cached_image = Cache::remember('contents_' . json_encode($request->query()), 60 * 24 * 365, function () use (&$request) {

            $path   = preg_replace('/(https?):\/\/(.*?)\/(.*)/', dirname(dirname(dirname(__DIR__))) . '/public/$3', $request->input('path'));
            $ext    = preg_replace('/^.*\.(.*)$/', '$1', $path);
            $star   = $request->input('star');
            $user   = $request->input('user');
            $amount = $request->input('amount');

            // 初期画像生成
            $imageSize = 400;
            $imageSize *= 3;
            $image = imagecreatetruecolor($imageSize, $imageSize);
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 127, 127, 127, 127);
            imagefill($image, 0, 0, $transparent);

            // 楕円描画
            $ellipseColor = imagecolorallocate($image, self::$colors[$amount][0], self::$colors[$amount][1], self::$colors[$amount][2]);
            imagefilledellipse($image, $imageSize / 2, $imageSize
                / 2, $imageSize, $imageSize, $ellipseColor);

            // コンテンツ画像書き込み
            if ($ext == 'png') {
                $contentsImage = imagecreatefrompng($path);
            } elseif ($ext == 'jpg' || $ext == 'jpeg') {
                $contentsImage = imagecreatefromjpeg($path);
            } elseif ($ext == 'gif') {
                $contentsImage = imagecreatefromgif($path);
            }
            $padding = 80;
            $padding *= 3;
            $ratio = imagesx($contentsImage) / imagesy($contentsImage);
            if ($ratio > 1) {
                // 横長
                imagecopyresized(
                    $image,
                    $contentsImage,
                    $padding,
                    $imageSize / 2 - ($imageSize - $padding * 2) / $ratio / 2,
                    0,
                    0,
                    $imageSize - $padding * 2,
                    ($imageSize - $padding * 2) / $ratio,
                    imagesx($contentsImage),
                    imagesy($contentsImage)
                );
            } else {
                // 縦長
                imagecopyresized(
                    $image,
                    $contentsImage,
                    $imageSize / 2 - ($imageSize - $padding * 2) * $ratio / 2,
                    $padding,
                    0,
                    0,
                    ($imageSize - $padding * 2) * $ratio,
                    $imageSize - $padding * 2,
                    imagesx($contentsImage),
                    imagesy($contentsImage)
                );
            }

            // フォント指定
            $roboto = dirname(dirname(dirname(__DIR__))) . '/resources/fonts/Roboto-Medium.ttf';

            // 文字色
            if (self::$colors[$amount][3]) {
                $textColor = imagecolorallocate($image, 255, 255, 255);
            } else {
                $textColor = imagecolorallocate($image, 16, 16, 16);
            }

            // スター名
            $starScreenName = User::findOrFail($star)->screen_name;
            $bbox           = imagettfbbox(60, 0, $roboto, $starScreenName);
            $w              = $bbox[2] - $bbox[0];
            $h              = $bbox[3] - $bbox[1];
            imagettftext($image, 60, 0, $imageSize / 2 - $w / 2, 180, $textColor, $roboto, $starScreenName);

            // ユーザー名
            if (User::find($user)) {
                $userScreenName = User::findOrFail($user)->screen_name;
                $bbox           = imagettfbbox(60, 0, $roboto, $userScreenName);
                $w              = $bbox[2] - $bbox[0];
                $h              = $bbox[3] - $bbox[1];
                imagettftext($image, 60, 0, $imageSize / 2 - $w / 2, $imageSize - 120, $textColor, $roboto, $userScreenName);
            }

            // スター数
            $bbox = imagettfbbox(80, 0, $roboto, $amount / 100);
            $w    = $bbox[2] - $bbox[0];
            $h    = $bbox[3] - $bbox[1];
            imagettftext($image, 80, 0, 110 - $w / 2, $imageSize / 2 - $h / 2, $textColor, $roboto, $amount / 100);

            // スター（右）
            $starImage = imagecreatefrompng(dirname(dirname(dirname(__DIR__))) . '/resources/images/star.png');
            imagecopyresized($image, $starImage, $imageSize - 180, $imageSize / 2 - imagesy($starImage) / 3, 0, 0, 120, 120, imagesx($starImage), imagesy($starImage));

            // アンチエイリアス
            $resolvedImage = imagecreatetruecolor($imageSize / 3, $imageSize / 3);
            imagesavealpha($resolvedImage, true);
            $transparent = imagecolorallocatealpha($resolvedImage, 127, 127, 127, 127);
            imagefill($resolvedImage, 0, 0, $transparent);

            imagecopyresampled($resolvedImage, $image, 0, 0, 0, 0, $imageSize / 3, $imageSize / 3, $imageSize, $imageSize);

            // レスポンス取得
            ob_start();

            imagepng($resolvedImage);

            $imageContents = ob_get_contents();
            ob_end_clean();

            // 破棄
            imagedestroy($image);
            imagedestroy($contentsImage);
            imagedestroy($resolvedImage);

            // 吐き出し
            return $imageContents;
        });

        return response($cached_image)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'max-age='. 60*60*24*7);

    }
}
