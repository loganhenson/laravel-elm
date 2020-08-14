module PAGE.Main exposing (..)

import Core
import Dict exposing (Dict)
import Html exposing (Html, div, text)
import Html.Attributes exposing (class)
import Json.Decode exposing (Decoder, Error, Value, dict, list, string, succeed)
import Json.Decode.Pipeline exposing (required)


type alias Props =
    { errors : Errors }


type alias State =
    {}


type alias Model =
    { props : Props
    , state : State
    }


type alias Errors =
    Dict String (List String)


type Msg
    = NewProps Value


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" (dict (list string))


initialState : Props -> State
initialState =
    \_ -> {}


main : Program Value { props : Result Error Props, state : Maybe State } Msg
main =
    Core.page
        { decodeProps = decodeProps
        , initialState = initialState
        , update = update
        , view = view
        , subscriptions = \_ -> Core.receiveNewProps NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Core.handleNewProps decodeProps props newProps, state = state }, Cmd.none )


view : Model -> Html Msg
view { props, state } =
    div [ class "container mx-auto m-4 p-4" ]
        [ text "Hello, PAGE." ]
