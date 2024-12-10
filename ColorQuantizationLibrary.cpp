//---------------------------------------------------------------------------
#include <vcl.h>
#include <stdint.h>
#include <memory>
#include <assert>

#pragma hdrstop

#include "ColorQuantizationLibrary.h"

//---------------------------------------------------------------------------

#pragma package(smart_init)




TOctreeNode::TOctreeNode(int Level, int ColorBits, int& LeafCount, TOctreeNode* ReducibleNodes[8])
    : PixelCount(0), RedSum(0), GreenSum(0), BlueSum(0), Next(NULL) {
    for (int i = 0; i < 8; ++i) {
        Child[i] = NULL;
    }

    IsLeaf = (Level == ColorBits);
    if (IsLeaf) {
        ++LeafCount;
    } else {
        Next = ReducibleNodes[Level];
        ReducibleNodes[Level] = this;
    }
}

TOctreeNode::~TOctreeNode() {
    for (int i = 0; i < 8; ++i) {
        delete Child[i];
    }
}


TColorQuantizer::TColorQuantizer(int MaxColors, int ColorBits)
    : FTree(NULL), FLeafCount(0), FMaxColors(MaxColors), FColorBits(ColorBits) {
    memset(FReducibleNodes, 0, sizeof(FReducibleNodes));
}

TColorQuantizer::~TColorQuantizer() {
    if (FTree) {
        DeleteTree(FTree);
    }
}

void TColorQuantizer::GetColorTable(TRGBQuad* RGBQuadArray) {
    int Index = 0;
    GetPaletteColors(FTree, RGBQuadArray, Index);
}

bool TColorQuantizer::ProcessImage(HANDLE Handle) {
    const int MaxPixelCount = 1048576;
    BITMAPINFO BitmapInfo;
    DIBSECTION DIBSection;
    RGBTRIPLE* ScanLine = NULL;

    int Bytes = GetObject(Handle, sizeof(DIBSection), &DIBSection);
    if (Bytes <= 0) return false;

    assert(DIBSection.dsBmih.biHeight < MaxPixelCount);
    assert(DIBSection.dsBmih.biWidth < MaxPixelCount);

    switch (DIBSection.dsBmih.biBitCount) {
        case 1:
        case 4:
        case 8:
            // ProcessLowBitDIB(); // Implement this case as needed
            break;
        case 16:
            // Process16BitDIB(); // Implement this case as needed
            break;
        case 24:
            ScanLine = reinterpret_cast<RGBTRIPLE*>(DIBSection.dsBm.bmBits);
            for (int j = 0; j < DIBSection.dsBmih.biHeight; ++j) {
                for (int i = 0; i < DIBSection.dsBmih.biWidth; ++i) {
                    AddColor(FTree, ScanLine[i].rgbtRed, ScanLine[i].rgbtGreen, ScanLine[i].rgbtBlue, FColorBits, 0, FLeafCount, FReducibleNodes);
                }
                // Reduce tree if necessary
                while (FLeafCount > FMaxColors) {
                    ReduceTree(FColorBits, FLeafCount, FReducibleNodes);
                }
                ScanLine = reinterpret_cast<RGBTRIPLE*>(reinterpret_cast<intptr_t>(ScanLine) + DIBSection.dsBm.bmWidthBytes);
            }
            break;
        case 32:
            // Process32BitDIB(); // Implement this case as needed
            break;
        default:
            return false;
    }
    return true;
}

void TColorQuantizer::AddColor(TOctreeNode*& Node, unsigned char r, unsigned char g, unsigned char b, int ColorBits, int Level, int& LeafCount, TOctreeNode* ReducibleNodes[8]) {
    static const unsigned char Mask[8] = {0x80, 0x40, 0x20, 0x10, 0x08, 0x04, 0x02, 0x01};

    if (!Node) {
        Node = new TOctreeNode(Level, ColorBits, LeafCount, ReducibleNodes);
    }

    if (Node->IsLeaf) {
        ++Node->PixelCount;
        Node->RedSum += r;
        Node->GreenSum += g;
        Node->BlueSum += b;
    } else {
        int Shift = 7 - Level;
        int Index = (((r & Mask[Level]) >> Shift) << 2) |
                    (((g & Mask[Level]) >> Shift) << 1) |
                    ((b & Mask[Level]) >> Shift);
        AddColor(Node->Child[Index], r, g, b, ColorBits, Level + 1, LeafCount, ReducibleNodes);
    }
}

void TColorQuantizer::DeleteTree(TOctreeNode*& Node) {
    for (int i = 0; i < 8; ++i) {
        if (Node->Child[i]) {
            DeleteTree(Node->Child[i]);
        }
    }
    delete Node;
    Node = NULL;
}

void TColorQuantizer::GetPaletteColors(TOctreeNode* Node, TRGBQuad* RGBQuadArray, int& Index) {
    if (Node->IsLeaf) {
        RGBQuadArray[Index].rgbRed = static_cast<BYTE>(Node->RedSum / Node->PixelCount);
        RGBQuadArray[Index].rgbGreen = static_cast<BYTE>(Node->GreenSum / Node->PixelCount);
        RGBQuadArray[Index].rgbBlue = static_cast<BYTE>(Node->BlueSum / Node->PixelCount);
        RGBQuadArray[Index].rgbReserved = 0;
        ++Index;
    } else {
        for (int i = 0; i < 8; ++i) {
            if (Node->Child[i]) {
                GetPaletteColors(Node->Child[i], RGBQuadArray, Index);
            }
        }
    }
}

void TColorQuantizer::ReduceTree(int ColorBits, int& LeafCount, TOctreeNode* ReducibleNodes[8]) {
    int i = ColorBits - 1;
    while (i > 0 && ReducibleNodes[i] == NULL) {
        --i;
    }

    TOctreeNode* Node = ReducibleNodes[i];
    ReducibleNodes[i] = Node->Next;

    int RedSum = 0, GreenSum = 0, BlueSum = 0, Children = 0;

    for (int j = 0; j < 8; ++j) {
        if (Node->Child[j]) {
            RedSum += Node->Child[j]->RedSum;
            GreenSum += Node->Child[j]->GreenSum;
            BlueSum += Node->Child[j]->BlueSum;
            Node->PixelCount += Node->Child[j]->PixelCount;
            delete Node->Child[j];
            Node->Child[j] = NULL;
            ++Children;
        }
    }

    Node->IsLeaf = true;
    Node->RedSum = RedSum;
    Node->GreenSum = GreenSum;
    Node->BlueSum = BlueSum;
    LeafCount -= (Children - 1);
}

