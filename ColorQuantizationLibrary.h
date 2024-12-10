//---------------------------------------------------------------------------

#ifndef ColorQuantizationLibraryH
#define ColorQuantizationLibraryH
//---------------------------------------------------------------------------
class TOctreeNode {
public:
    bool IsLeaf;
    int PixelCount;
    int RedSum;
    int GreenSum;
    int BlueSum;
    TOctreeNode* Next;
    TOctreeNode* Child[8];

    // Constructor and Destructor
    TOctreeNode(int Level, int ColorBits, int& LeafCount, TOctreeNode* ReducibleNodes[8]);
    ~TOctreeNode();

    // Utility methods
    void AddColor(unsigned char r, unsigned char g, unsigned char b, int ColorBits, int Level, int& LeafCount, TOctreeNode* ReducibleNodes[8]);
};
class TColorQuantizer {
private:
    TOctreeNode* FTree;
    int FLeafCount;
    TOctreeNode* FReducibleNodes[8];
    int FMaxColors;
    int FColorBits;

    void AddColor(TOctreeNode*& Node, unsigned char r, unsigned char g, unsigned char b, int ColorBits, int Level, int& LeafCount, TOctreeNode* ReducibleNodes[8]);
    void DeleteTree(TOctreeNode*& Node);
    void GetPaletteColors(TOctreeNode* Node, TRGBQuad* RGBQuadArray, int& Index);
    void ReduceTree(int ColorBits, int& LeafCount, TOctreeNode* ReducibleNodes[8]);

public:
    TColorQuantizer(int MaxColors, int ColorBits);
    ~TColorQuantizer();

    void GetColorTable(TRGBQuad* RGBQuadArray);
    bool ProcessImage(HANDLE Handle);
};
//---------------------------------------------------------------------------
#endif
